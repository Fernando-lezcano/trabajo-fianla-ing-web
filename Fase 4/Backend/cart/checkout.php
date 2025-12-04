<?php
// backend/cart/checkout.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../BD/conexion.php';

function json_response($ok, $msg, $extra = []) {
    echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(false, 'Método no permitido.');
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    json_response(false, 'Debes iniciar sesión para comprar.');
}

$userId = (int)$_SESSION['user_id'];

// Campos de tarjeta (solo se valida que no estén vacíos)
$cardName   = trim($_POST['card_name']   ?? '');
$cardNumber = trim($_POST['card_number'] ?? '');
$cardExp    = trim($_POST['card_exp']    ?? '');
$cardCvv    = trim($_POST['card_cvv']    ?? '');

if (!$cardName || !$cardNumber || !$cardExp || !$cardCvv) {
    http_response_code(400);
    json_response(false, 'Por favor completa todos los datos de la tarjeta.');
}

try {
    // 1) Obtener carrito activo
    $sqlCart = "
        SELECT id
        FROM carts
        WHERE user_id = :uid
          AND status  = 'active'
        LIMIT 1
    ";
    $stmtCart = $pdo->prepare($sqlCart);
    $stmtCart->execute([':uid' => $userId]);
    $cart = $stmtCart->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        json_response(false, 'No hay carrito activo para este usuario.');
    }

    $cartId = (int)$cart['id'];

    // 2) Obtener items del carrito
    $sqlItems = "
        SELECT ci.product_id, ci.quantity, ci.unit_price,
               p.stock
        FROM cart_items ci
        INNER JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = :cid
    ";
    $stmtItems = $pdo->prepare($sqlItems);
    $stmtItems->execute([':cid' => $cartId]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        json_response(false, 'Tu carrito está vacío.');
    }

    // 3) Verificar stock y calcular montos
    $subtotal = 0;
    foreach ($items as $it) {
        if ($it['quantity'] > $it['stock']) {
            json_response(false, 'No hay stock suficiente para uno de los productos.');
        }
        $subtotal += $it['quantity'] * $it['unit_price'];
    }

    $shipping = $subtotal > 0 ? 20 : 0;
    $total    = $subtotal + $shipping;

    // 4) Crear orden + order_items en una transacción
    $pdo->beginTransaction();

    $sqlOrder = "
        INSERT INTO orders (user_id, cart_id, total_amount, shipping_amount, status)
        VALUES (:uid, :cid, :total, :shipping, 'paid')
    ";
    $stmtOrder = $pdo->prepare($sqlOrder);
    $stmtOrder->execute([
        ':uid'      => $userId,
        ':cid'      => $cartId,
        ':total'    => $total,
        ':shipping' => $shipping
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $sqlOrderItem = "
        INSERT INTO order_items (order_id, product_id, quantity, unit_price)
        VALUES (:oid, :pid, :qty, :price)
    ";
    $stmtOrderItem = $pdo->prepare($sqlOrderItem);

    $sqlUpdateStock = "
        UPDATE products
        SET stock = stock - :qty
        WHERE id = :pid
    ";
    $stmtUpdateStock = $pdo->prepare($sqlUpdateStock);

    foreach ($items as $it) {
        $stmtOrderItem->execute([
            ':oid'   => $orderId,
            ':pid'   => $it['product_id'],
            ':qty'   => $it['quantity'],
            ':price' => $it['unit_price']
        ]);

        // Actualizar stock
        $stmtUpdateStock->execute([
            ':qty' => $it['quantity'],
            ':pid' => $it['product_id']
        ]);
    }

    // 5) Marcar carrito como convertido y limpiar items
    $sqlUpdCart = "
        UPDATE carts
        SET status = 'converted', updated_at = NOW()
        WHERE id = :cid
    ";
    $stmtUpdCart = $pdo->prepare($sqlUpdCart);
    $stmtUpdCart->execute([':cid' => $cartId]);

    $sqlDelItems = "DELETE FROM cart_items WHERE cart_id = :cid";
    $stmtDelItems = $pdo->prepare($sqlDelItems);
    $stmtDelItems->execute([':cid' => $cartId]);

    $pdo->commit();

    json_response(true, 'Compra realizada con éxito.', [
        'order_id' => $orderId,
        'total'    => $total
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    json_response(false, 'Error en la base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    json_response(false, 'Error inesperado: ' . $e->getMessage());
}
