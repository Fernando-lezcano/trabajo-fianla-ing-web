<?php
// html/cart.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../BD/conexion.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    // Redirigir a login o mostrar mensaje
    header('Location: account.html');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// 1) Obtener carrito activo
$sqlCart = "
    SELECT id 
    FROM carts
    WHERE user_id = :uid AND status = 'active'
    LIMIT 1
";
$stmtCart = $pdo->prepare($sqlCart);
$stmtCart->execute([':uid' => $userId]);
$cart = $stmtCart->fetch(PDO::FETCH_ASSOC);

$items = [];
$subtotal = 0;

if ($cart) {
    $cartId = (int)$cart['id'];

    // 2) Obtener items del carrito + datos del producto
    $sqlItems = "
        SELECT 
            ci.id         AS cart_item_id,
            ci.product_id AS product_id,
            ci.quantity   AS quantity,
            ci.unit_price AS unit_price,
            p.name        AS product_name,
            p.short_description,
            p.image_path,
            p.stock       AS stock
        FROM cart_items ci
        INNER JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = :cid
    ";
    $stmtItems = $pdo->prepare($sqlItems);
    $stmtItems->execute([':cid' => $cartId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {
        $subtotal += $it['unit_price'] * $it['quantity'];
    }
} else {
    $cartId = null;
}

$shipping = $subtotal > 0 ? 20 : 0;
$total    = $subtotal + $shipping;
$totalItems = 0;
foreach ($items as $it) {
    $totalItems += (int)$it['quantity'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RockStore - Carrito</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
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

    <!-- 🔵 NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="home.php">🎸 RockStore</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-3">
                <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>

                <li class="nav-item dropdown hover-dropdown">
                    <a class="nav-link dropdown-toggle" id="storeMenu" role="button">Tienda</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="instrumentos.html">Instrumentos</a></li>
                        <li><a class="dropdown-item" href="ropaMujer.html">Ropa Mujer</a></li>
                        <li><a class="dropdown-item" href="ropaHombre.html">Ropa Hombre</a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="offers.php">Ofertas</a></li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                <li class="nav-item"><a class="nav-link" href="accountusuario.php"><i class="fas fa-user"></i> Cuenta</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">

        <a href="home.html" class="btn btn-link">← Continuar comprando</a>

        <div class="row">

            <!-- 🛒 IZQUIERDA -->
            <div class="col-lg-7">

                <h3>Cart Shopping</h3>
                <p>Tienes <span id="items-count"><?= $totalItems ?></span> ítems en tu carrito</p>

                <?php if (empty($items)): ?>
                    <div class="alert alert-info">
                        Tu carrito está vacío.
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="product-item d-flex align-items-center justify-content-between p-3 border rounded mb-3"
                             data-item-id="<?= (int)$item['cart_item_id'] ?>"
                             data-price="<?= htmlspecialchars($item['unit_price']) ?>"
                             data-max-stock="<?= (int)$item['stock'] ?>">

                            <div class="d-flex align-items-center">
                                <div class="product-image1 bg-light rounded me-3" style="width:80px;height:80px;">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <p class="product-name m-0 fw-bold"><?= htmlspecialchars($item['product_name']) ?></p>
                                    <p class="product-details m-0 text-muted">
                                        <?= htmlspecialchars($item['short_description'] ?? '') ?>
                                    </p>

                                    <div class="quantity-controls d-flex align-items-center mt-2">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="decreaseQty(this)">-</button>
                                        <span class="qty-value mx-2"><?= (int)$item['quantity'] ?></span>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="increaseQty(this)">+</button>
                                        <small class="text-muted ms-2">(Stock: <?= (int)$item['stock'] ?>)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <p class="product-price fw-bold">
                                    $<span class="item-total">
                                        <?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                                    </span>
                                </p>
                                <span class="delete-btn text-danger" onclick="removeItem(this)" style="cursor:pointer;">🗑️</span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

            <!-- 🧾 DERECHA -->
            <div class="col-lg-5">
                <div class="checkout-card border rounded p-4 shadow-sm">

                    <h5>Detalles de tu compra</h5>

                    <!-- Tipo de tarjeta -->
                    <label class="form-label">Tipo de tarjeta</label>
                    <div class="mb-3">
                        <img src="../img2/visa.png" width="50" class="tarjeta-icono">
                        <img src="../img2/tarjeta.png" width="50" class="tarjeta-icono">
                    </div>

                    <label class="form-label">Name on card</label>
                    <input class="form-control" type="text" id="card-name">

                    <label class="form-label mt-3">Card number</label>
                    <input class="form-control" type="text" id="card-number">

                    <div class="row mt-3">
                        <div class="col">
                            <label class="form-label">Expiration date</label>
                            <input class="form-control" type="text" id="card-exp">
                        </div>

                        <div class="col">
                            <label class="form-label">CVV</label>
                            <input class="form-control" type="text" id="card-cvv">
                        </div>
                    </div>

                    <hr class="my-4">

                    <p>Subtotal 
                        <span class="float-end" id="subtotal">
                            $<?= number_format($subtotal, 2) ?>
                        </span>
                    </p>
                    <p>Shipping <span class="float-end">$<?= number_format($shipping, 2) ?></span></p>

                    <h5 class="mt-3">Total (incl. taxes) 
                        <span class="float-end" id="total">
                            $<?= number_format($total, 2) ?>
                        </span>
                    </h5>

                    <button class="btn btn-primary w-100 mt-4" onclick="checkout()">
                        Comprar
                    </button>

                </div>
            </div>

        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function recalcTotals() {
        let subtotal = 0;
        let totalItems = 0;

        document.querySelectorAll('.product-item').forEach(item => {
            const price = parseFloat(item.dataset.price);
            const qty   = parseInt(item.querySelector('.qty-value').textContent);
            subtotal += price * qty;
            totalItems += qty;

            const itemTotalEl = item.querySelector('.item-total');
            if (itemTotalEl) {
                itemTotalEl.textContent = (price * qty).toFixed(2);
            }
        });

        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        const shipping = subtotal > 0 ? 20 : 0;
        document.getElementById('total').textContent = '$' + (subtotal + shipping).toFixed(2);
        document.getElementById('items-count').textContent = totalItems;
    }

    function increaseQty(btn) {
        const itemEl   = btn.closest('.product-item');
        const qtySpan  = itemEl.querySelector('.qty-value');
        const maxStock = parseInt(itemEl.dataset.maxStock);
        let qty        = parseInt(qtySpan.textContent);

        if (qty >= maxStock) {
            alert('No hay más stock disponible para este producto.');
            return;
        }

        const newQty   = qty + 1;
        const itemId   = itemEl.dataset.itemId;

        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('quantity', newQty);

        fetch('../backend/cart/update_item.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                qtySpan.textContent = newQty;
                recalcTotals();
            } else {
                alert(data.msg || 'No se pudo actualizar la cantidad.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión con el servidor.');
        });
    }

    function decreaseQty(btn) {
        const itemEl   = btn.closest('.product-item');
        const qtySpan  = itemEl.querySelector('.qty-value');
        let qty        = parseInt(qtySpan.textContent);

        if (qty <= 1) {
            return; // si quieres que 0 elimine, aquí podrías llamar a removeItem
        }

        const newQty   = qty - 1;
        const itemId   = itemEl.dataset.itemId;

        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('quantity', newQty);

        fetch('../backend/cart/update_item.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                qtySpan.textContent = newQty;
                recalcTotals();
            } else {
                alert(data.msg || 'No se pudo actualizar la cantidad.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión con el servidor.');
        });
    }

    function removeItem(btn) {
        const itemEl = btn.closest('.product-item');
        const itemId = itemEl.dataset.itemId;

        const formData = new FormData();
        formData.append('item_id', itemId);

        fetch('../backend/cart/remove_item.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                itemEl.remove();
                recalcTotals();

                if (document.querySelectorAll('.product-item').length === 0) {
                    location.reload();
                }
            } else {
                alert(data.msg || 'No se pudo eliminar el producto del carrito.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión con el servidor.');
        });
    }

    function checkout() {
        const name  = document.getElementById('card-name').value.trim();
        const num   = document.getElementById('card-number').value.trim();
        const exp   = document.getElementById('card-exp').value.trim();
        const cvv   = document.getElementById('card-cvv').value.trim();

        if (!name || !num || !exp || !cvv) {
            alert('Por favor completa todos los datos de la tarjeta.');
            return;
        }

        // Verificar que haya productos
        if (!document.querySelector('.product-item')) {
            alert('Tu carrito está vacío.');
            return;
        }

        const formData = new FormData();
        formData.append('card_name', name);
        formData.append('card_number', num);
        formData.append('card_exp', exp);
        formData.append('card_cvv', cvv);

        fetch('../backend/cart/checkout.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                showToast('Compra realizada con éxito 🎸 ¡Gracias por tu pedido!');
                // Dar un pequeño delay antes de recargar para que el usuario vea el toast
                setTimeout(() => location.reload(), 1500);
            } else {
                alert(data.msg || 'No se pudo completar la compra.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión con el servidor.');
        });
    }

    // Recalcular por si acaso
    recalcTotals();
    </script>
    
    <script>
        function showToast(message) {
            const toast = document.createElement("div");
            toast.classList.add("toast-success");
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => toast.classList.add("toast-show"), 100);

            setTimeout(() => {
                toast.classList.remove("toast-show");
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
