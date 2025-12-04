<?php
// backend/productos/eliminar.php
// Script para eliminar productos de la base de datos

// Establece el tipo de contenido como JSON con codificación UTF-8
// Esto asegura que el cliente reciba una respuesta bien formateada
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos
// __DIR__ garantiza una ruta absoluta desde el directorio actual
require_once __DIR__ . '/../../BD/conexion.php';


// VALIDACIÓN DEL MÉTODO HTTP


// Este script solo debe ser accedido mediante POST por seguridad
// GET podría permitir eliminaciones accidentales mediante enlaces
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Código 405: Método no permitido
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit; // Termina la ejecución del script
}


// FUNCIÓN AUXILIAR PARA RESPUESTAS


/**
 * Función helper que envía una respuesta JSON y termina la ejecución
 * 
 * @param bool $success   Indica si la operación fue exitosa
 * @param string $message Mensaje descriptivo para el cliente
 * @param array $extra    Datos adicionales a incluir en la respuesta
 */
function response_and_exit($success, $message, $extra = [])
{
    // Combina el array base con los datos adicionales
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit; // Importante: termina la ejecución después de enviar la respuesta
}


// LÓGICA PRINCIPAL


try {
    
    // VALIDACIÓN DEL ID DEL PRODUCTO
 
    
    // Obtiene el ID del producto desde los datos POST
    // (int) convierte el valor a entero para seguridad
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Verifica que el ID sea un número positivo
    // Esto previene IDs inválidos como 0, -1, o no numéricos
    if ($id <= 0) {
        response_and_exit(false, 'ID de producto inválido.');
    }

   
    // VERIFICACIÓN DE EXISTENCIA (OPTIONAL CHECK)
  
    
    // Consulta preparada para verificar que el producto existe
    // Esto evita mensajes de error confusos si el producto ya fue eliminado
    $sqlCheck = "SELECT id FROM products WHERE id = :id";
    $stmtCheck = $pdo->prepare($sqlCheck); // Prepara la consulta
    $stmtCheck->execute([':id' => $id]);   // Ejecuta con parámetro seguro
    $exists = $stmtCheck->fetch();         // Obtiene el resultado

    // Si no existe, informa al cliente sin intentar eliminarlo
    if (!$exists) {
        response_and_exit(false, 'El producto no existe o ya fue eliminado.');
    }

    
    // ELIMINACIÓN DEL PRODUCTO

    
    // NOTA: Esta es una eliminación permanente (HARD DELETE)
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $pdo->prepare($sql);       // Prepara la consulta
    $stmt->execute([':id' => $id]);    // Ejecuta con parámetro seguro
    
    // Si llegamos aquí, la eliminación fue exitosa
    response_and_exit(true, 'Producto eliminado correctamente.');


// MANEJO DE EXCEPCIONES


} catch (PDOException $e) {
    // Errores específicos de la base de datos (PDO)
    // Ejemplo: conexión perdida, error de sintaxis SQL, violación de clave foránea
    http_response_code(500); // Error interno del servidor
    response_and_exit(false, 'Error en la base de datos: ' . $e->getMessage());
    
} catch (Exception $e) {
    // Errores generales no relacionados con PDO
    // Ejemplo: errores de PHP, memoria insuficiente, etc.
    http_response_code(500);
    response_and_exit(false, 'Error inesperado: ' . $e->getMessage());
}