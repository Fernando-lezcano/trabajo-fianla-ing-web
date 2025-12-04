<?php
// backend/auth/logout.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eliminar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

// Si viene por fetch, no pasa nada que devolvamos algo sencillo
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => true,
    'msg' => 'Sesión cerrada correctamente.'
], JSON_UNESCAPED_UNICODE);
