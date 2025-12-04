<?php
// backend/auth/login.php
// Script para autenticación de usuarios mediante correo electrónico y contraseña

header('Content-Type: application/json; charset=utf-8');

// 1. INICIALIZACIÓN DE SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../BD/conexion.php';

// 3. FUNCIÓN AUXILIAR PARA RESPUESTAS
function response_and_exit(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. VALIDACIÓN DEL MÉTODO HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    response_and_exit(false, 'Método no permitido. Usa POST.');
}

try {
    // 4.1. RECEPCIÓN Y LIMPIEZA DE CREDENCIALES
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // 4.2. VALIDACIÓN DE DATOS DE ENTRADA
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        response_and_exit(false, 'El correo electrónico no es válido.');
    }

    if ($password === '') {
        response_and_exit(false, 'La contraseña es obligatoria.');
    }

    // 4.3. BÚSQUEDA DEL USUARIO EN LA BASE DE DATOS
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.email,
            u.password_hash,
            r.name AS role_name
        FROM users u
        INNER JOIN roles r ON u.role_id = r.id
        WHERE u.email = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4.4. VERIFICACIÓN DE EXISTENCIA DEL USUARIO
    if (!$user) {
        response_and_exit(false, 'Correo o contraseña incorrectos.');
    }

    // 4.5. VERIFICACIÓN DE CONTRASEÑA
    if (!password_verify($password, $user['password_hash'])) {
        response_and_exit(false, 'Correo o contraseña incorrectos.');
    }

    // 🔥 4.6. CREACIÓN DE LA SESIÓN DE USUARIO
    $_SESSION['user_id']    = (int)$user['id'];   // ⬅️ ESTE ES EL QUE NECESITA EL CARRITO
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role_name'];

    // 4.7. DETERMINACIÓN DE REDIRECCIÓN SEGÚN ROL
    if ($user['role_name'] === 'admin') {
        $redirect = 'AdminViewInventory.html';
    } else {
        $redirect = 'store.html';
    }

    // 4.8. RESPUESTA EXITOSA
    response_and_exit(true, 'Inicio de sesión exitoso.', [
        'role'     => $user['role_name'],
        'redirect' => $redirect
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    response_and_exit(false, 'Error en la base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    response_and_exit(false, 'Error inesperado: ' . $e->getMessage());
}
