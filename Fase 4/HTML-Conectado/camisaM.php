<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RockStore - Página</title>

    <!-- Bootstrap CSS -->
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
        <a href="account.html">Iniciar sesión</a>
        <a href="account.html">Registrarse</a>
        <input type="text" placeholder="Buscar...">
    </div>

    <!-- 🔵 NAVBAR PRINCIPAL -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="../html/home.php">🎸 RockStore</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav ms-3">

            <li class="nav-item"><a class="nav-link" href="../html/home.php">Home</a></li>

            <!-- 🔽 Dropdown con hover -->
            <li class="nav-item dropdown hover-dropdown">
                <a class="nav-link dropdown-toggle" href="../html/store.html" id="storeMenu" role="button">
                    Tienda
                </a>

                <ul class="dropdown-menu" aria-labelledby="storeMenu">
                    <li><a class="dropdown-item" href="../html/instrumentos.html">Instrumentos</a></li>
                    <li><a class="dropdown-item" href="../html/ropaMujer.html">Ropa Mujer</a></li>
                    <li><a class="dropdown-item" href="../html/ropaHombre.html">Ropa Hombre</a></li>
                </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="offers.php">Ofertas</a></li>
        </ul>
        <!-- Enlaces Cart y Account alineados a la derecha -->
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



<body>

    <!-- CATÁLOGO DE PRODUCTOS -->
    <div class="catalog-section">
        <h6 class="section-title">Lo más popular hasta ahora</h6>

        <div class="products-grid">
            <!-- Camisa Rock Queen -->

                <?php
                // Incluir el catálogo reutilizable
                require_once __DIR__ . '/../backend/productos/catalogo.php';

                // Cargar productos de la subcategoría "cuerda"
                renderProductosGrid([
                    'subcategory_slug' => 'camisas-mujer'   // ESTE slug debe coincidir con el de tu tabla subcategories
                ]);
                ?>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cart.js"></script>
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
        <!-- Script para menús -->
    <script>
        function toggleSubmenu(id) {
            document.getElementById(id).classList.toggle("show-submenu");
        }
    </script>
    
</body>

</html>