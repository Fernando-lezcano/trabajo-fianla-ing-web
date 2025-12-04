<?php
// backend/productos/listar.php
// Script para obtener y listar todos los productos de la base de datos

// Establece el tipo de contenido como JSON con codificación UTF-8
// Esto asegura que el cliente reciba una respuesta en formato JSON con caracteres especiales correctamente codificados
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos
// __DIR__ proporciona la ruta absoluta del directorio actual del script
require_once __DIR__ . '/../../BD/conexion.php';


// BLOQUE PRINCIPAL DE EJECUCIÓN


try {

    // 1. CONSULTA SQL PARA OBTENER PRODUCTOS

    
    // Consulta que obtiene productos junto con información de su subcategoría
    // Usa alias (AS) para nombres más descriptivos en los resultados
    $sql = "
        SELECT 
            p.id,                    -- ID único del producto
            p.code       AS codigo,  -- Código del producto (alias para claridad)
            p.name       AS producto, -- Nombre del producto
            sc.name      AS subcategoria_nombre, -- Nombre de la subcategoría
            sc.slug      AS subcategoria_slug,   -- Slug/URL amigable de la subcategoría
            p.price      AS precio,  -- Precio del producto
            p.stock,                 -- Cantidad en inventario
            p.status,                -- Estado: 'en_stock', 'stock_bajo', 'agotado'
            p.image_path,            -- Ruta de la imagen del producto
            p.has_offer,             -- Indica si tiene oferta (1/0)
            p.offer_type,            -- Tipo de oferta: 'porcentaje' o 'precio_fijo'
            p.offer_value            -- Valor de la oferta
        FROM products p  -- Tabla principal de productos (alias 'p')
        INNER JOIN subcategories sc ON p.subcategory_id = sc.id  -- Une con subcategorías
        ORDER BY p.id ASC  -- Ordena por ID ascendente (del más antiguo al más nuevo)
    ";


    // 2. EJECUCIÓN DE LA CONSULTA

    
    // Ejecuta la consulta SQL directamente (sin parámetros variables)
    $stmt = $pdo->query($sql);
    
    // Obtiene todos los resultados como array asociativo
    // Cada fila es un array con los nombres de columna como claves
    $rows = $stmt->fetchAll();

    // 3. PROCESAMIENTO Y TRANSFORMACIÓN DE DATOS
    
    $products = []; // Array que contendrá los productos procesados

    // Itera sobre cada fila de resultados para transformar los datos
    foreach ($rows as $row) {
        // Construye un array con la estructura esperada por el frontend
        $products[] = [
            'id'          => (int)$row['id'],           // Convierte a entero
            'codigo'      => $row['codigo'],            // Código del producto
            'producto'    => $row['producto'],          // Nombre del producto
            
            // NOTA IMPORTANTE: En el frontend (JS) se llama "categoria", 
            // pero en realidad es el nombre de la SUBCATEGORÍA
            // Esto podría causar confusión - considera renombrarlo en el frontend
            'categoria'   => $row['subcategoria_nombre'],
            
            // Slug de la subcategoría (útil para URLs o filtros)
            'subcategoriaSlug'=> $row['subcategoria_slug'],
            
            'precio'      => (float)$row['precio'],     // Convierte a número decimal
            'stock'       => (int)$row['stock'],        // Convierte a entero
            
            // Convierte el estado de la BD a etiqueta legible para humanos
            // Ejemplo: 'en_stock' → 'En Stock'
            'estado'      => mapStatusToLabel($row['status']),
            
            // Ruta de la imagen (puede ser null si no hay imagen)
            // Ejemplo: '/productos/vestido_darkqueen.png'
            'imagen'      => $row['image_path'],
            
            // Convierte el valor numérico (1/0) a booleano (true/false)
            'tieneOferta' => (bool)$row['has_offer'],
            
            // Tipo de oferta: 'porcentaje', 'precio_fijo' o null
            'tipoOferta'  => $row['offer_type'],
            
            // Valor de la oferta (convertido a float si existe)
            'valorOferta' => isset($row['offer_value']) ? (float)$row['offer_value'] : null
        ];
    }

    // 4. ENVÍO DE LA RESPUESTA
    
    // Convierte el array de productos a formato JSON
    // JSON_UNESCAPED_UNICODE: mantiene caracteres Unicode sin escaparlos (ej: ñ, á, é)
    echo json_encode($products, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // MANEJO DE ERRORES DE BASE DE DATOS

    
    // Código HTTP 500: Error interno del servidor
    http_response_code(500);
    
    // Envía una respuesta de error en formato JSON
    echo json_encode([
        'error'   => true,  // Indica que ocurrió un error
        'message' => 'Error al obtener productos: ' . $e->getMessage()  // Mensaje descriptivo
    ], JSON_UNESCAPED_UNICODE);
    
    exit; // Termina la ejecución del script
}


// FUNCIÓN AUXILIAR PARA MAPEO DE ESTADOS

/**
 * Convierte el valor ENUM de la base de datos a una etiqueta legible para el frontend
 * 
 * Esta función realiza la transformación inversa de lo que se hace en guardar.php
 * 
 * @param string $status Valor de estado de la base de datos
 * @return string Etiqueta legible para humanos
 */
function mapStatusToLabel(string $status): string
{
    switch ($status) {
        case 'en_stock':
            return 'En Stock';      // Producto disponible
        case 'stock_bajo':
            return 'Stock bajo';    // Quedan pocas unidades
        case 'agotado':
            return 'Agotado';       // Sin existencias
        default:
            return 'Desconocido';   // Valor no reconocido (caso de seguridad)
    }
}