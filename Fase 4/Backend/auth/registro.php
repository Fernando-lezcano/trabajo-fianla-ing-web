<?php
// backend/auth/registro.php
// Script para registrar nuevos usuarios en el sistema

// Establece el tipo de contenido como JSON con codificación UTF-8
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos
require_once __DIR__ . '/../../BD/conexion.php';

// VALIDACIÓN DEL MÉTODO HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Función que envía una respuesta JSON y termina la ejecución
 */
function response_and_exit(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// LÓGICA PRINCIPAL DEL REGISTRO
try {
    // RECEPCIÓN Y LIMPIEZA DE DATOS DEL FORMULARIO
    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // NUEVOS CAMPOS - con valores por defecto
    $phone    = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $country  = isset($_POST['country']) ? trim($_POST['country']) : '';
    $address  = isset($_POST['address']) ? trim($_POST['address']) : '';

    // VALIDACIÓN DE DATOS DE ENTRADA
    $errors = [];

    if ($name === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo electrónico no es válido.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    // NUEVA VALIDACIÓN: País es obligatorio
    if ($country === '') {
        $errors[] = 'El país es obligatorio.';
    }

    // Si hay errores de validación, se devuelven al frontend
    if (!empty($errors)) {
        response_and_exit(false, 'Errores de validación.', ['errors' => $errors]);
    }

    // VERIFICACIÓN DE EMAIL EXISTENTE
    $sqlCheck = "SELECT id FROM users WHERE email = :email LIMIT 1";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':email' => $email]);

    if ($stmtCheck->fetch()) {
        response_and_exit(false, 'Ya existe una cuenta registrada con ese correo.');
    }

    // OBTENCIÓN DEL ROL "CLIENTE"
    $sqlRole = "SELECT id FROM roles WHERE name = 'cliente' LIMIT 1";
    $stmtRole = $pdo->prepare($sqlRole);
    $stmtRole->execute();
    $role = $stmtRole->fetch();

    if (!$role) {
        response_and_exit(false, 'No se encontró el rol de cliente en la base de datos.');
    }

    $roleId = (int)$role['id'];

    // HASH DE CONTRASEÑA
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // INSERCIÓN DEL NUEVO USUARIO - CON CAMPOS ACTUALIZADOS
    $sqlInsert = "
        INSERT INTO users (
            role_id, 
            name, 
            email, 
            password_hash, 
            phone, 
            country, 
            address, 
            member_since, 
            is_active, 
            created_at, 
            updated_at
        ) VALUES (
            :role_id, 
            :name, 
            :email, 
            :password_hash, 
            :phone, 
            :country, 
            :address, 
            CURDATE(), 
            TRUE, 
            NOW(), 
            NOW()
        )
    ";
    
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        ':role_id'       => $roleId,
        ':name'          => $name,
        ':email'         => $email,
        ':password_hash' => $passwordHash,
        ':phone'         => $phone ?: null,       // Si está vacío, insertar NULL
        ':country'       => $country,
        ':address'       => $address ?: null      // Si está vacío, insertar NULL
    ]);

    // Obtener el ID del nuevo usuario
    $newUserId = (int)$pdo->lastInsertId();

    // RESPUESTA EXITOSA
    response_and_exit(true, 'Registro exitoso. Ahora puedes iniciar sesión.', [
        'user_id' => $newUserId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    response_and_exit(false, 'Error en la base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    response_and_exit(false, 'Error inesperado: ' . $e->getMessage());
}