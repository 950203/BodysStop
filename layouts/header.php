<?php
$count = array_sum($_SESSION['cart'] ?? []);
?>

<header>
    <h2><a href="/">Bodys_chic</a></h2>

    <nav>
        <a href="/?c=Producto&m=index">Tienda</a>

        <div class="cart-wrapper">
            <a href="/?c=Carrito&m=index" class="cart-link">
                🛒 <span id="cart-count">0</span>
            </a>

            <div id="mini-cart" class="mini-cart"></div>
        </div>
        <a href="/?c=AdminProducto&m=index" class="admin-link">
            ⚙️ Admin
        </a>
    </nav>
</header>


<script>
    function updateCartCount() {
        fetch('/?c=Carrito&m=count')
            .then(res => res.text())
            .then(count => {
                const el = document.getElementById('cart-count');
                if (!el) return;

                el.textContent = count;
                el.classList.add('pop');
                setTimeout(() => el.classList.remove('pop'), 300);
            });
    }

    function loadMiniCart() {
        fetch('/?c=Carrito&m=mini')
            .then(res => res.text())
            .then(html => {
                document.getElementById('mini-cart').innerHTML = html;
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateCartCount();
        loadMiniCart();
    });
</script>