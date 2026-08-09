// Validación en el cliente: reglas espejo de las del servidor.
// Los formularios se registran en VALIDADORES por selector.

function vEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
}

function vClave(v) {
    if (v.length < 8) return 'La contraseña debe tener mínimo 8 caracteres';
    if (!/[A-Z]/.test(v)) return 'La contraseña debe tener una mayúscula';
    if (!/[a-z]/.test(v)) return 'La contraseña debe tener una minúscula';
    if (!/[0-9]/.test(v)) return 'La contraseña debe tener un número';
    return null;
}

function marcarError(input, msg) {
    input.classList.add('is-invalid');
    let div = input.parentElement.querySelector('.invalid-feedback');
    if (!div) {
        div = document.createElement('div');
        div.className = 'invalid-feedback';
        input.parentElement.appendChild(div);
    }
    div.textContent = msg;
    return false;
}

function limpiarErrores(form) {
    form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(d => d.remove());
}

function campoValido(input, validador) {
    const ok = validador(input.value.trim());
    if (ok !== true && ok !== null) {
        marcarError(input, ok);
        return false;
    }
    return true;
}

const VALIDADORES = {
    // Login
    '#login-form': form => {
        const email = form.querySelector('[name=email]');
        const ok = vEmail(email.value.trim());
        if (!ok) return marcarError(email, 'Ingresa un correo válido');
        return true;
    },
    // Registro
    '#register-form': form => {
        const nombre = form.querySelector('[name=nombre]');
        const email = form.querySelector('[name=email]');
        const clave = form.querySelector('[name=clave]');
        const confirm = form.querySelector('[name=clave_confirm]');

        if (nombre.value.trim().length < 3) return marcarError(nombre, 'El nombre debe tener al menos 3 caracteres');
        if (!vEmail(email.value.trim())) return marcarError(email, 'Ingresa un correo válido');
        const err = vClave(clave.value);
        if (err) return marcarError(clave, err);
        if (clave.value !== confirm.value) return marcarError(confirm, 'Las contraseñas no coinciden');
        return true;
    },
    // Restablecer contraseña
    '#reset-form': form => {
        const clave = form.querySelector('[name=clave]');
        const confirm = form.querySelector('[name=clave_confirm]');
        const err = vClave(clave.value);
        if (err) return marcarError(clave, err);
        if (clave.value !== confirm.value) return marcarError(confirm, 'Las contraseñas no coinciden');
        return true;
    },
    // Solicitar recuperación
    '#forgot-form': form => {
        const email = form.querySelector('[name=email]');
        if (!vEmail(email.value.trim())) return marcarError(email, 'Ingresa un correo válido');
        return true;
    },
    // Checkout
    '#checkoutForm': form => {
        const nombre = form.querySelector('[name=nombre]');
        const email = form.querySelector('[name=email]');
        const dir = form.querySelector('[name=direccion]');
        if (nombre.value.trim().length < 3) return marcarError(nombre, 'Escribe tu nombre completo');
        if (!vEmail(email.value.trim())) return marcarError(email, 'Ingresa un correo válido');
        if (dir.value.trim().length < 5) return marcarError(dir, 'Escribe una dirección válida');
        return true;
    },
    // Producto (admin): nombre, precio y al menos una talla con stock
    'form[data-validar-producto]': form => {
        const nombre = form.querySelector('[name=nombre]');
        const precio = form.querySelector('[name=precio]');
        if (nombre.value.trim().length < 3) return marcarError(nombre, 'El nombre debe tener al menos 3 caracteres');
        if (!(parseFloat(precio.value) > 0)) return marcarError(precio, 'Ingresa un precio mayor a 0');
        const tallas = [...form.querySelectorAll('[name^="talla["]')];
        if (tallas.length === 0 || !tallas.some(t => parseInt(t.value) > 0)) {
            Swal.fire({ icon: 'warning', title: 'Stock', text: 'Define stock mayor a 0 en al menos una talla' });
            return false;
        }
        return true;
    },
};

document.addEventListener('DOMContentLoaded', () => {
    Object.entries(VALIDADORES).forEach(([selector, validador]) => {
        const form = document.querySelector(selector);
        if (!form) return;

        form.addEventListener('submit', e => {
            limpiarErrores(form);
            const ok = validador(form);
            if (ok !== true) {
                e.preventDefault();
            }
        });
    });
});
