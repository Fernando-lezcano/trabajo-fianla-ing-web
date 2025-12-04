<?php
// backend/cart/update_item.php
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
    json_response(false, 'Debes iniciar sesión.');
}

$userId   = (int)$_SESSION['user_id'];
$itemId   = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($itemId <= 0 || $quantity <= 0) {
    http_response_code(400);
    json_response(false, 'Datos inválidos.');
}

try {
    // Verificar que el item pertenece a un carrito activo del usuario
    $sql = "
        SELECT ci.id, ci.cart_id, ci.product_id, ci.quantity,
               p.stock
        FROM cart_items ci
        INNER JOIN carts c ON ci.cart_id = c.id
        INNER JOIN products p ON ci.product_id = p.id
        WHERE ci.id = :iid
          AND c.user_id = :uid
          AND c.status = 'active'
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':iid' => $itemId,
        ':uid' => $userId
    ]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        json_response(false, 'Item no encontrado en tu carrito.');
    }

    $stock = (int)$item['stock'];
    if ($quantity > $stock) {
        json_response(false, 'No hay suficiente stock disponible.');
    }

    $sqlUpd = "
        UPDATE cart_items
        SET quantity = :qty, updated_at = NOW()
        WHERE id = :iid
    ";
    $stmtUpd = $pdo->prepare($sqlUpd);
    $stmtUpd->execute([
        ':qty' => $quantity,
        ':iid' => $itemId
    ]);

    json_response(true, 'Cantidad actualizada.');

} catch (PDOException $e) {
    http_response_code(500);
    json_response(false, 'Error en la base de datos: ' . $e->getMessage());
}
