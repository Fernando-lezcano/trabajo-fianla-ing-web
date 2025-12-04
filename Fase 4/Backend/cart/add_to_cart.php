<?php
// backend/cart/add_to_cart.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../BD/conexion.php'; // aquí tienes $pdo

function json_response($ok, $msg, $extra = [])
{
    echo json_encode(array_merge([
        'ok'  => $ok,
        'msg' => $msg,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// 1) Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(false, 'Método no permitido');
}

// 2) Verificar login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    json_response(false, 'Debes iniciar sesión para agregar productos al carrito.');
}

$userId    = (int)$_SESSION['user_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($productId <= 0 || $quantity <= 0) {
    http_response_code(400);
    json_response(false, 'Datos inválidos.');
}

try {
    // 3) Obtener producto y calcular precio final (con oferta si aplica)
    $sqlProd = "
        SELECT price, has_offer, offer_type, offer_value, offer_start, offer_end
        FROM products
        WHERE id = :id
        LIMIT 1
    ";
    $stmtProd = $pdo->prepare($sqlProd);
    $stmtProd->execute([':id' => $productId]);
    $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        http_response_code(404);
        json_response(false, 'Producto no encontrado.');
    }

    $basePrice = (float)$prod['price'];
    $unitPrice = $basePrice;

    if ((int)$prod['has_offer'] === 1 && !empty($prod['offer_type']) && $prod['offer_value'] !== null) {
        $ahora = new DateTimeImmutable();
        $enRango = true;

        if (!empty($prod['offer_start'])) {
            $inicio = new DateTimeImmutable($prod['offer_start']);
            if ($ahora < $inicio) $enRango = false;
        }
        if (!empty($prod['offer_end'])) {
            $fin = new DateTimeImmutable($prod['offer_end']);
            if ($ahora > $fin) $enRango = false;
        }

        if ($enRango) {
            $offerValue = (float)$prod['offer_value'];

            if ($prod['offer_type'] === 'porcentaje') {
                $unitPrice = max(0, $basePrice - ($basePrice * ($offerValue / 100)));
            } elseif ($prod['offer_type'] === 'precio_fijo') {
                $unitPrice = max(0, $offerValue);
            }
        }
    }

    // 4) Buscar o crear carrito activo
    $sqlCart = "
        SELECT id FROM carts
        WHERE user_id = :uid AND status = 'active'
        LIMIT 1
    ";
    $stmtCart = $pdo->prepare($sqlCart);
    $stmtCart->execute([':uid' => $userId]);
    $cart = $stmtCart->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cartId = (int)$cart['id'];
    } else {
        $sqlNewCart = "INSERT INTO carts (user_id, status) VALUES (:uid, 'active')";
        $stmtNew = $pdo->prepare($sqlNewCart);
        $stmtNew->execute([':uid' => $userId]);
        $cartId = (int)$pdo->lastInsertId();
    }

    // 5) Insertar o actualizar cart_items
    $sqlItem = "
        SELECT id, quantity
        FROM cart_items
        WHERE cart_id = :cid AND product_id = :pid
        LIMIT 1
    ";
    $stmtItem = $pdo->prepare($sqlItem);
    $stmtItem->execute([
        ':cid' => $cartId,
        ':pid' => $productId,
    ]);
    $item = $stmtItem->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $newQty = (int)$item['quantity'] + $quantity;

        $sqlUpd = "
            UPDATE cart_items
            SET quantity = :qty, updated_at = NOW()
            WHERE id = :id
        ";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute([
            ':qty' => $newQty,
            ':id'  => (int)$item['id'],
        ]);

        json_response(true, 'Cantidad actualizada en el carrito.', [
            'cart_id'    => $cartId,
            'quantity'   => $newQty,
            'unit_price' => $unitPrice,
        ]);
    } else {
        $sqlIns = "
            INSERT INTO cart_items (cart_id, product_id, quantity, unit_price)
            VALUES (:cid, :pid, :qty, :price)
        ";
        $stmtIns = $pdo->prepare($sqlIns);
        $stmtIns->execute([
            ':cid'   => $cartId,
            ':pid'   => $productId,
            ':qty'   => $quantity,
            ':price' => $unitPrice,
        ]);

        json_response(true, 'Producto agregado al carrito.', [
            'cart_id'    => $cartId,
            'quantity'   => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    json_response(false, 'Error en la base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    json_response(false, 'Error inesperado: ' . $e->getMessage());
}
