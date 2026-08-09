function headers() {
    const h = { 'Content-Type': 'application/x-www-form-urlencoded' };
    if (window.API_TOKEN) h['X-Auth-Token'] = window.API_TOKEN;
    if (window.CSRF_TOKEN) h['X-CSRF-Token'] = window.CSRF_TOKEN;
    return h;
}

function requiereLogin() {
    if (!window.API_TOKEN) {
        window.location.href = '/?c=Auth&m=login';
        return true;
    }
    return false;
}

function notificar(data) {
    if (data.ok) {
        updateCartCount();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Agregado al carrito',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        }
    } else if (data.error && typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'error', title: 'No se pudo agregar', text: data.error });
    }
}

function addToCart(id, talla, qty) {
    if (requiereLogin()) return;

    const body = new URLSearchParams({ id: id });
    if (talla) body.set('talla', talla);
    if (qty) body.set('qty', qty);

    fetch('/?c=Carrito&m=agregar', {
        method: 'POST',
        headers: headers(),
        body: body
    })
    .then(r => r.json())
    .then(notificar);
}

function updateCartCount() {
    if (!window.API_TOKEN) return;

    fetch('/?c=Carrito&m=count', { headers: headers() })
        .then(r => r.text())
        .then(count => {
            const el = document.getElementById('cart-count');
            if (el) {
                el.textContent = count;
                el.classList.add('pop');
                setTimeout(() => el.classList.remove('pop'), 300);
            }
        });
}

function removeFromCart(clave) {
    fetch('/?c=Carrito&m=remove', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave)
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
    });
}

function clearCart() {
    fetch('/?c=Carrito&m=clear', { method: 'POST', headers: headers() })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
    });
}

function updateQty(clave, qty) {
    fetch('/?c=Carrito&m=updateQty', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave) + '&qty=' + qty
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
    });
}

function sumar(clave) {
    fetch('/?c=Carrito&m=sumar', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave)
    }).then(() => location.reload());
}

function restar(clave) {
    fetch('/?c=Carrito&m=restar', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave)
    }).then(() => location.reload());
}

function removeItem(clave) {
    fetch('/?c=Carrito&m=remove', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave)
    })
    .then(res => res.json())
    .then(() => {
        const row = document.getElementById('row-' + CSS.escape(clave));
        if (row) {
            row.classList.add('row-fade-out');
            setTimeout(() => {
                row.remove();
                updateCartCount();
                recalcularTotal();
            }, 400);
        }
    });
}

function recalcularTotal() {
    const el = document.querySelector('.cart-total strong');
    if (!el) return;
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(td => {
        total += parseInt(td.textContent.replace(/\D/g, ''));
    });
    el.textContent = 'Total: $' + total.toLocaleString();
}

function changeMiniQty(clave, delta) {
    const span = document.querySelector('#mini-item-' + CSS.escape(clave) + ' .qty');
    if (!span) return;
    let qty = parseInt(span.textContent) + delta;
    if (qty <= 0) {
        removeMini(clave);
        return;
    }
    fetch('/?c=Carrito&m=updateQty', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave) + '&qty=' + qty
    })
    .then(r => r.json())
    .then(() => { updateCartCount(); location.reload(); });
}

function removeMini(clave) {
    fetch('/?c=Carrito&m=remove', {
        method: 'POST',
        headers: headers(),
        body: 'id=' + encodeURIComponent(clave)
    })
    .then(r => r.json())
    .then(() => { updateCartCount(); location.reload(); });
}

document.addEventListener('DOMContentLoaded', updateCartCount);
