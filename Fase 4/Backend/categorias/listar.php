<?php
// backend/categorias/listar.php

// Establece el tipo de contenido como JSON con codificación UTF-8
// para asegurar que los caracteres especiales se muestren correctamente
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos
// __DIR__ garantiza que la ruta sea absoluta y relativa al directorio actual
require_once __DIR__ . '/../../BD/conexion.php';

// Bloque try-catch para manejar posibles excepciones
try {
    // Consulta SQL para obtener subcategorías con información de sus categorías
    // JOIN para relacionar subcategorías con sus categorías correspondientes
    $sql = "
        SELECT 
            sc.id,                           -- ID de la subcategoría
            sc.name       AS subcategory_name, -- Nombre de la subcategoría
            sc.slug       AS subcategory_slug, -- Slug/URL amigable de la subcategoría
            c.id          AS category_id,    -- ID de la categoría principal
            c.name        AS category_name,  -- Nombre de la categoría principal
            c.slug        AS category_slug   -- Slug/URL amigable de la categoría
        FROM subcategories sc                -- Tabla de subcategorías (alias 'sc')
        INNER JOIN categories c ON sc.category_id = c.id  -- Relación con categorías
        ORDER BY c.id, sc.id                -- Ordena primero por categoría, luego por subcategoría
    ";

    // Ejecuta la consulta directamente (sin parámetros)
    $stmt = $pdo->query($sql);
    
    // Obtiene todos los resultados como array asociativo
    $rows = $stmt->fetchAll();

    // Convierte los resultados a JSON y los imprime
    // JSON_UNESCAPED_UNICODE: preserva caracteres Unicode sin escaparlos
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // En caso de error en la base de datos:
    
    // Establece código de respuesta HTTP 500 (Error interno del servidor)
    http_response_code(500);
    
    // Devuelve un objeto JSON con información del error
    echo json_encode([
        'success' => false,                    // Indica que la operación falló
        'message' => 'Error al obtener subcategorías: ' . $e->getMessage()  // Mensaje de error
    ], JSON_UNESCAPED_UNICODE);
}