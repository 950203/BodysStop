function addToCart(id) {
    fetch('/?c=Carrito&m=agregar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            updateCartCount();
            Swal.fire({
                icon: 'success',
                title: 'Agregado al carrito',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });
}

function updateCartCount() {
    fetch('/?c=Carrito&m=count')
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

function removeFromCart(id) {
    fetch('/?c=Carrito&m=remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
    });
}

function clearCart() {
    fetch('/?c=Carrito&m=clear', { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
    });
}

function updateQty(id, qty) {
    fetch('/?c=Carrito&m=updateQty', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&qty=' + qty
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) location.reload();
    });
}

function sumar(id) {
    fetch('/?c=Carrito&m=sumar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    }).then(() => location.reload());
}

function restar(id) {
    fetch('/?c=Carrito&m=restar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    }).then(() => location.reload());
}

function removeItem(id) {
    fetch('/?c=Carrito&m=remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(res => res.json())
    .then(() => {
        const row = document.getElementById('row-' + id);
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

function changeMiniQty(id, delta) {
    const span = document.querySelector('#mini-item-' + id + ' .qty');
    if (!span) return;
    let qty = parseInt(span.textContent) + delta;
    if (qty <= 0) {
        removeMini(id);
        return;
    }
    fetch('/?c=Carrito&m=updateQty', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&qty=' + qty
    })
    .then(r => r.json())
    .then(() => { updateCartCount(); location.reload(); });
}

function removeMini(id) {
    fetch('/?c=Carrito&m=remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(() => { updateCartCount(); location.reload(); });
}

document.addEventListener('DOMContentLoaded', updateCartCount);
