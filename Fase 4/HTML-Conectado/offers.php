<?php
// html/offers.php
// Muestra TODOS los productos que tengan oferta activa en la BD

// No es obligatorio usar sesión aquí, a menos que quieras mostrar el nombre del usuario.
// Si luego quieres condicionar algo por login, puedes activar esto:
// if (session_status() === PHP_SESSION_NONE) { session_start(); }

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RockStore - Ofertas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

    <!-- 🔵 METANAVEGACIÓN -->
    <div class="top-meta">
        <a href="login.html">Iniciar sesión</a>
        <a href="register.html">Registrarse</a>
        <input type="text" placeholder="Buscar...">
    </div>

    <!-- 🔵 NAVBAR PRINCIPAL -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="home.php">🎸 RockStore</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>

                <!-- 🔽 Dropdown -->
                <li class="nav-item dropdown hover-dropdown">
                    <a class="nav-link dropdown-toggle" id="storeMenu" role="button">
                        Tienda
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="storeMenu">
                        <li><a class="dropdown-item" href="instrumentos.html">Instrumentos</a></li>
                        <li><a class="dropdown-item" href="ropaMujer.html">Ropa Mujer</a></li>
                        <li><a class="dropdown-item" href="ropaHombre.html">Ropa Hombre</a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link active" href="offers.php">Ofertas</a></li>
            </ul>

            <!-- A la derecha -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> Carrito
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="accountusuario.php">
                        <i class="fas fa-user"></i> Cuenta
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="container my-5">

        <div class="catalog-section">
            <h2 class="section-title">Ofertas especiales</h2>
            <p class="text-muted mb-4">
                Aquí se muestran únicamente los productos que tienen descuento activo en la base de datos.
            </p>

            <div class="products-grid">
                <?php
                // Incluir el catálogo reutilizable
                require_once __DIR__ . '/../backend/productos/catalogo.php';

                // Mostrar SOLO productos con oferta (has_offer = 1)
                renderProductosGrid([
                    'only_offers' => true
                ]);
                ?>
            </div>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script 
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

    <!-- Script del carrito -->
    <script src="../js/cart.js"></script>

    <!-- Favoritos -->
    <script>
        function toggleFavorite(btn) {
            btn.classList.toggle('active');
            const icon = btn.querySelector('i');
            if (btn.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        }
    </script>

    <!-- (Solo si usas submenús con toggleSubmenu en otras páginas)
    <script>
        function toggleSubmenu(id) {
            document.getElementById(id).classList.toggle("show-submenu");
        }
    </script>
    -->

</body>
</html>
