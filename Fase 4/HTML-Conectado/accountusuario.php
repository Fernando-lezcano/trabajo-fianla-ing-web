<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../BD/conexion.php';

// Si no hay usuario logueado, lo mandamos al login
if (!isset($_SESSION['user_id'])) {
    header('Location: account.html');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// 1) Datos del usuario
$sqlUser = "
    SELECT 
        name,
        email,
        phone,
        country,
        address,
        member_since
    FROM users
    WHERE id = :id
    LIMIT 1
";
$stmtUser = $pdo->prepare($sqlUser);
$stmtUser->execute([':id' => $userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Si por alguna razón no existe el usuario, forzamos logout
    header('Location: ../backend/auth/logout.php');
    exit;
}

// Formatear fecha "miembro desde"
$memberSinceText = 'N/D';
if (!empty($user['member_since'])) {
    $fecha = new DateTime($user['member_since']);
    // Ej: Enero 2023
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $mes = $meses[(int)$fecha->format('n')] ?? $fecha->format('m');
    $memberSinceText = $mes . ' ' . $fecha->format('Y');
}

// 2) Pedidos del usuario
$sqlOrders = "
    SELECT 
        id,
        total_amount,
        shipping_amount,
        status,
        created_at
    FROM orders
    WHERE user_id = :uid
    ORDER BY created_at DESC
";
$stmtOrders = $pdo->prepare($sqlOrders);
$stmtOrders->execute([':uid' => $userId]);
$allOrders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

// Últimos 3 pedidos para la sección "recientes"
$recentOrders = array_slice($allOrders, 0, 3);

// Helper para mostrar estado en texto y badge
function getOrderStatusLabel(string $status): array {
    // status en BD: 'pending','paid','shipped','cancelled'
    switch ($status) {
        case 'shipped':
            return ['Enviado', 'badge-shipping'];
        case 'paid':
            return ['Pagado', 'badge-delivered'];
        case 'pending':
            return ['Pendiente', 'badge-processing'];
        case 'cancelled':
        default:
            return ['Cancelado', 'badge-cancelled'];
    }
}

function formatOrderDate(string $datetime): string {
    $dt = new DateTime($datetime);
    return $dt->format('d M Y'); // Ej: 15 Nov 2025
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - RockStore</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tu CSS -->
    <link rel="stylesheet" href="../css/style2.css">
</head>

<body>

<header>
    <!-- METANAVEGACIÓN -->
    <div class="top-meta">
        <a href="account.html">Iniciar sesión</a>
        <a href="account.html">Registrarse</a>
        <input type="text" placeholder="Buscar...">
    </div>

    <!-- NAVBAR PRINCIPAL -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="home.php">🎸 RockStore</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>

                <!-- Dropdown -->
                <li class="nav-item dropdown hover-dropdown">
                    <a class="nav-link dropdown-toggle" href="store.html" id="storeMenu" role="button">
                        Tienda
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="storeMenu">
                        <li><a class="dropdown-item" href="instrumentos.html">Instrumentos</a></li>
                        <li><a class="dropdown-item" href="ropaMujer.html">Ropa Mujer</a></li>
                        <li><a class="dropdown-item" href="ropaHombre.html">Ropa Hombre</a></li>
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
                    <a class="nav-link active" href="accountusuario.php">
                        <i class="fas fa-user"></i> Cuenta
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

<main class="container my-5 account-user-page">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- User Profile Card -->
            <div class="account-card shadow-lg mb-4">
                <div class="account-header">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <h3 class="mb-1">
                            <?= htmlspecialchars($user['name']) ?>
                        </h3>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar-alt"></i>
                            Miembro desde <?= htmlspecialchars($memberSinceText) ?>
                        </p>
                    </div>
                </div>

                <hr class="my-4">

                <div class="account-details">
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-envelope"></i> Email
                        </div>
                        <div class="detail-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-phone"></i> Teléfono
                        </div>
                        <div class="detail-value">
                            <?= htmlspecialchars($user['phone'] ?? 'No registrado') ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-map-marker-alt"></i> País
                        </div>
                        <div class="detail-value">
                            <?= htmlspecialchars($user['country'] ?? 'No registrado') ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-home"></i> Dirección
                        </div>
                        <div class="detail-value">
                            <?= htmlspecialchars($user['address'] ?? 'No registrado') ?>
                        </div>
                    </div>
                </div>

                <div class="account-actions mt-4">
                    <button class="btn btn-vermas" onclick="alert('Función de editar perfil pendiente')">
                        <i class="fas fa-edit"></i> Editar Perfil
                    </button>
                    <button class="btn-account-logout" onclick="logoutUser()">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </button>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="account-card shadow-lg">
                <h5 class="orders-title">
                    <i class="fas fa-box"></i> Mis Pedidos Recientes
                </h5>

                <div class="orders-list">
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-muted">Aún no has realizado pedidos.</p>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): 
                            [$statusLabel, $badgeClass] = getOrderStatusLabel($order['status']);
                        ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-name">
                                        Pedido #<?= (int)$order['id'] ?> - $<?= number_format($order['total_amount'], 2) ?>
                                    </div>
                                    <div class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= formatOrderDate($order['created_at']) ?>
                                    </div>
                                </div>
                                <span class="<?= $badgeClass ?>">
                                    <?= htmlspecialchars($statusLabel) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button class="btn-view-all mt-3" data-bs-toggle="modal" data-bs-target="#ordersModal">
                    Ver Todos los Pedidos <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</main>

<!-- MODAL HISTORIAL DE PEDIDOS -->
<div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ordersModalLabel">
            Historial de pedidos
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <?php if (empty($allOrders)): ?>
            <p class="text-muted">No tienes pedidos registrados.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allOrders as $order): 
                            [$statusLabel, $badgeClass] = getOrderStatusLabel($order['status']);
                        ?>
                            <tr>
                                <td>#<?= (int)$order['id'] ?></td>
                                <td><?= formatOrderDate($order['created_at']) ?></td>
                                <td>
                                    <span class="<?= $badgeClass ?>">
                                        <?= htmlspecialchars($statusLabel) ?>
                                    </span>
                                </td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="footer-brand">RockStore</h5>
                <p class="mb-0">Tienda de instrumentos, ropa y accesorios. Música para todos.</p>
            </div>

            <div class="col-md-4 mb-4">
                <h6>Enlaces</h6>
                <ul class="list-unstyled">
                    <li><a href="instrumentos.html">Instrumentos</a></li>
                    <li><a href="ropaMujer.html">Ropa Mujer</a></li>
                    <li><a href="ropaHombre.html">Ropa Hombre</a></li>
                    <li><a href="offers.php">Ofertas</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h6>Contacto</h6>
                <p class="mb-1">Email: <a href="mailto:info@rockstore.local">info@rockstore.local</a></p>
                <p class="mb-1">Tel: +52 55 1234 5678</p>
                <div class="social mt-2">
                    <a href="#" class="me-2"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#"><i class="fab fa-twitter fa-lg"></i></a>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 small text-muted">© 2025 RockStore — Todos los derechos reservados</div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Cerrar sesión con fetch y redirección
function logoutUser() {
    fetch('../backend/auth/logout.php', {
        method: 'POST'
    })
    .then(() => {
        window.location.href = 'home.php';
    })
    .catch(() => {
        window.location.href = 'home.php';
    });
}
</script>

</body>
</html>
