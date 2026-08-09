<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="checkout-container">
    <h1>Checkout</h1>

    <form id="checkoutForm">
        <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: María Pérez" required>
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" placeholder="Ej: maria@gmail.com" required>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección de envío</label>
            <textarea id="direccion" name="direccion" rows="3" placeholder="Ej: Cra 123 # 45-67" required></textarea>
        </div>

        <button type="submit" class="btn-checkout">
            <i class="fas fa-lock me-2"></i>Confirmar compra
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/validacion.js"></script>
<script>
    document.getElementById('checkoutForm').addEventListener('submit', e => {
        e.preventDefault();

        const form = e.target;
        const data = new URLSearchParams(new FormData(form));

        const h = { 'Content-Type': 'application/x-www-form-urlencoded' };
        if (window.API_TOKEN) h['X-Auth-Token'] = window.API_TOKEN;
        if (window.CSRF_TOKEN) h['X-CSRF-Token'] = window.CSRF_TOKEN;

        fetch('/?c=Checkout&m=process', {
            method: 'POST',
            headers: h,
            body: data
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Compra exitosa',
                    text: 'Gracias por tu compra',
                    timer: 2500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '/';
                });
            } else {
                Swal.fire('Error', res.error || 'Ocurrió un error', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo procesar la compra', 'error');
        });
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
