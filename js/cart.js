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
    fetch('/?c=Carrito&m=clear', {
        method: 'POST'
    })
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

document.addEventListener('DOMContentLoaded', updateCartCount);
