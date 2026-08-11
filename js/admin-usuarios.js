function apiPost(url, body, cb) {
    const h = { 'Content-Type': 'application/x-www-form-urlencoded' };
    if (window.API_TOKEN) h['X-Auth-Token'] = window.API_TOKEN;
    if (window.CSRF_TOKEN) h['X-CSRF-Token'] = window.CSRF_TOKEN;

    fetch(url, { method: 'POST', headers: h, body })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                if (cb) cb(data);
            } else {
                Swal.fire('Error', data.error || 'Ocurrió un error', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'No se pudo completar la acción', 'error'));
}

document.addEventListener('DOMContentLoaded', () => {
    // Cambiar rol
    document.querySelectorAll('.rol-select').forEach(select => {
        select.addEventListener('change', () => {
            apiPost('/?c=AdminUsuario&m=cambiarRol', 'id=' + select.dataset.id + '&rol=' + select.value, () => {
                Swal.fire({ icon: 'success', title: 'Rol actualizado', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            });
        });
    });

    // Activar / desactivar
    document.querySelectorAll('.activo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const nuevoActivo = btn.dataset.activo === '1' ? '0' : '1';
            const accion = nuevoActivo === '1' ? 'activar' : 'desactivar';
            Swal.fire({
                title: '¿' + accion.charAt(0).toUpperCase() + accion.slice(1) + ' cuenta?',
                text: 'El usuario podrá ' + (nuevoActivo === '1' ? 'iniciar sesión de nuevo.' : 'no iniciar sesión.'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#000',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ' + accion,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    apiPost('/?c=AdminUsuario&m=cambiarActivo', 'id=' + btn.dataset.id + '&activo=' + nuevoActivo, () => location.reload());
                }
            });
        });
    });

    // Restablecer contraseña
    document.querySelectorAll('.pass-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            Swal.fire({
                title: 'Nueva contraseña para ' + btn.dataset.nombre,
                input: 'password',
                inputAttributes: { autocapitalize: 'off', placeholder: 'Mín. 8 caracteres, mayúscula, minúscula y número' },
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    apiPost('/?c=AdminUsuario&m=resetPassword', 'id=' + btn.dataset.id + '&clave=' + encodeURIComponent(result.value), () => {
                        Swal.fire({ icon: 'success', title: 'Contraseña actualizada', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    });
                }
            });
        });
    });

    // Mostrar / ocultar contraseña
    document.querySelectorAll('.pass-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const span = document.querySelector('.pass-text[data-id="' + btn.dataset.id + '"]');
            if (!span) return;
            if (span.classList.toggle('pass-hidden')) {
                span.textContent = '••••••••';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                span.textContent = btn.dataset.valor;
                btn.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
});
