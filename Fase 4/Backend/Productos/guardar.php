<?php
// backend/productos/guardar.php
// Script para crear o actualizar productos en la base de datos

// Establece el tipo de contenido como JSON con codificación UTF-8
// Esto asegura que el cliente (frontend) reciba una respuesta bien formateada
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos
// __DIR__ garantiza una ruta absoluta desde el directorio actual del script
require_once __DIR__ . '/../../BD/conexion.php';

// VALIDACIÓN DEL MÉTODO HTTP

// Este script solo debe ser accedido mediante POST
// Los métodos GET podrían exponer datos sensibles o causar acciones no deseadas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Código 405: Método no permitido
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit; // Termina la ejecución del script inmediatamente
}


// FUNCIÓN AUXILIAR PARA RESPUESTAS

/**
 * Función helper que envía una respuesta JSON y termina la ejecución
 * 
 * @param bool $success   Indica si la operación fue exitosa (true/false)
 * @param string $message Mensaje descriptivo para el cliente
 * @param array $extra    Datos adicionales a incluir en la respuesta (opcional)
 */
function response_and_exit($success, $message, $extra = [])
{
    // Combina el array base con los datos adicionales usando array_merge
    // Esto permite extender la respuesta con datos específicos
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit; // Importante: termina la ejecución después de enviar la respuesta
}

// LÓGICA PRINCIPAL

try {
    // 1. RECEPCIÓN Y LIMPIEZA DE DATOS DEL FORMULARIO

    
    // ID del producto: si viene, es una actualización; si no, es una creación
    // (int) convierte el valor a entero para seguridad
    $idProducto   = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    // trim() elimina espacios en blanco al inicio y final
    $codigo       = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $nombre       = isset($_POST['producto']) ? trim($_POST['producto']) : '';
    
    // IMPORTANTE: Se recibe el slug de la subcategoría, no su ID
    // Esto es más amigable y evita problemas con valores numéricos
    $slugSubcat   = isset($_POST['subcategoria']) ? trim($_POST['subcategoria']) : '';
    
    // Conversión a tipos numéricos apropiados
    $precio       = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
    $stock        = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $estadoLabel  = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    // DATOS DE OFERTA (CAMPOS OPCIONALES)
    
    // Verifica si el producto tiene oferta activa
    $tieneOferta  = isset($_POST['tieneOferta']) && $_POST['tieneOferta'] === '1';
    
    // Tipo de oferta: 'porcentaje' o 'precio_fijo'
    $tipoOferta   = isset($_POST['tipoOferta']) ? trim($_POST['tipoOferta']) : null;
    
    // Valor de la oferta (solo si tiene oferta y no está vacío)
    $valorOferta  = isset($_POST['valorOferta']) && $_POST['valorOferta'] !== '' 
                    ? (float)$_POST['valorOferta'] 
                    : null;


    // 2. VALIDACIONES BÁSICAS DE DATOS

    
    $errores = []; // Array para acumular mensajes de error

    if ($codigo === '') {
        $errores[] = 'El código es obligatorio.';
    }
    if ($nombre === '') {
        $errores[] = 'El nombre del producto es obligatorio.';
    }
    if ($precio <= 0) {
        $errores[] = 'El precio debe ser mayor a 0.';
    }
    if ($stock < 0) {
        $errores[] = 'El stock no puede ser negativo.';
    }
    if ($slugSubcat === '') {
        $errores[] = 'Debes seleccionar una subcategoría.';
    }

    // Validaciones específicas para ofertas
    if ($tieneOferta) {
        if ($tipoOferta !== 'porcentaje' && $tipoOferta !== 'precio_fijo') {
            $errores[] = 'Tipo de oferta inválido.';
        }
        if ($valorOferta === null || $valorOferta <= 0) {
            $errores[] = 'Debes indicar un valor de oferta mayor a 0.';
        }
    }

    // Si hay errores, se devuelven al frontend
    if (!empty($errores)) {
        response_and_exit(false, 'Errores de validación.', ['errors' => $errores]);
    }


    // 3. MAPEO DE ESTADO (Frontend → Base de datos)
    
    // El frontend envía etiquetas legibles: "En Stock", "Stock bajo", "Agotado"
    // La base de datos espera valores ENUM: 'en_stock', 'stock_bajo', 'agotado'
    $estadoBD = null;
    switch ($estadoLabel) {
        case 'En Stock':
            $estadoBD = 'en_stock';
            break;
        case 'Stock bajo':
            $estadoBD = 'stock_bajo';
            break;
        case 'Agotado':
            $estadoBD = 'agotado';
            break;
        default:
            // Valor por defecto si no coincide
            $estadoBD = 'en_stock';
    }

    // 4. OBTENCIÓN DE IDs DE CATEGORÍA/SUBCATEGORÍA
  
    
    // Se busca en la base de datos la subcategoría por su slug
    // Esto es más seguro que recibir directamente el ID (evita manipulaciones)
    $sqlSub = "SELECT id, category_id FROM subcategories WHERE slug = :slug LIMIT 1";
    $stmtSub = $pdo->prepare($sqlSub);
    $stmtSub->execute([':slug' => $slugSubcat]);
    $subcat = $stmtSub->fetch();

    // Si no se encuentra la subcategoría, se informa el error
    if (!$subcat) {
        response_and_exit(false, 'La subcategoría seleccionada no existe en la base de datos.');
    }

    // Extrae los IDs necesarios para la inserción
    $subcategoryId = (int)$subcat['id'];
    $categoryId    = (int)$subcat['category_id'];

    // 5. MANEJO DE IMÁGENES (SUBIIDA/ACTUALIZACIÓN)
    
    $imagePath = null; // Ruta de la imagen en la base de datos

    // Si es una edición, se verifica si ya existe una imagen
    if ($idProducto) {
        $sqlImg = "SELECT image_path FROM products WHERE id = :id";
        $stmtImg = $pdo->prepare($sqlImg);
        $stmtImg->execute([':id' => $idProducto]);
        $rowImg = $stmtImg->fetch();
        if ($rowImg) {
            // Mantiene la imagen existente si no se sube una nueva
            $imagePath = $rowImg['image_path'];
        }
    }

    // Procesamiento de nueva imagen subida
    // $_FILES['imagen']['error'] === UPLOAD_ERR_OK significa que el archivo se subió correctamente
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['imagen']['tmp_name'];  // Ruta temporal del archivo
        $origName = $_FILES['imagen']['name'];     // Nombre original del archivo

        // Obtiene la extensión del archivo
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $validExt = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Extensiones permitidas

        // Valida la extensión del archivo
        if (!in_array($ext, $validExt)) {
            response_and_exit(false, 'Formato de imagen no válido. Usa JPG, PNG, GIF o WEBP.');
        }

        // Genera un nombre único para evitar colisiones
        $newFileName = uniqid('prod_', true) . '.' . $ext;

        // Determina la ruta del directorio de destino
        // realpath() obtiene la ruta absoluta canónica
        $targetDir  = realpath(__DIR__ . '/../../productos');
        if ($targetDir === false) {
            response_and_exit(false, 'No se encontró el directorio de productos en el servidor.');
        }

        // Ruta completa del archivo destino
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $newFileName;

        // Mueve el archivo temporal a su ubicación final
        if (!move_uploaded_file($tmpName, $targetPath)) {
            response_and_exit(false, 'No se pudo guardar la imagen subida.');
        }

        // Ruta relativa que se almacenará en la base de datos
        // Ejemplo: '/productos/prod_5f1a2b3c4d5e6.jpg'
        $imagePath = '/productos/' . $newFileName;
    }


    // 6. PREPARACIÓN DE DATOS DE OFERTA PARA BD
    
    
    // Convierte valores booleanos a enteros para la base de datos
    $hasOffer   = $tieneOferta ? 1 : 0;
    $offerType  = $tieneOferta ? $tipoOferta : null;  // Solo se guarda si hay oferta
    $offerValue = $tieneOferta ? $valorOferta : null; // Solo se guarda si hay oferta

    
    // 7. OPERACIÓN EN BASE DE DATOS (INSERT/UPDATE)
    
    
    if ($idProducto) {
        // ACTUALIZACIÓN (UPDATE) 
        $sql = "
            UPDATE products
            SET 
                category_id   = :category_id,
                subcategory_id= :subcategory_id,
                code          = :code,
                name          = :name,
                price         = :price,
                stock         = :stock,
                status        = :status,
                image_path    = :image_path,
                has_offer     = :has_offer,
                offer_type    = :offer_type,
                offer_value   = :offer_value,
                updated_at    = NOW()  -- Actualiza la fecha de modificación
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id'    => $categoryId,
            ':subcategory_id' => $subcategoryId,
            ':code'           => $codigo,
            ':name'           => $nombre,
            ':price'          => $precio,
            ':stock'          => $stock,
            ':status'         => $estadoBD,
            ':image_path'     => $imagePath,
            ':has_offer'      => $hasOffer,
            ':offer_type'     => $offerType,
            ':offer_value'    => $offerValue,
            ':id'             => $idProducto
        ]);

        response_and_exit(true, 'Producto actualizado correctamente.', [
            'id' => $idProducto,
            'image_path' => $imagePath  // Devuelve la ruta de la imagen al frontend
        ]);

    } else {
        //  CREACIÓN (INSERT) 
        $sql = "
            INSERT INTO products
                (category_id, subcategory_id, code, name, price, stock, status,
                 image_path, has_offer, offer_type, offer_value, created_at, updated_at)
            VALUES
                (:category_id, :subcategory_id, :code, :name, :price, :stock, :status,
                 :image_path, :has_offer, :offer_type, :offer_value, NOW(), NOW())
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id'    => $categoryId,
            ':subcategory_id' => $subcategoryId,
            ':code'           => $codigo,
            ':name'           => $nombre,
            ':price'          => $precio,
            ':stock'          => $stock,
            ':status'         => $estadoBD,
            ':image_path'     => $imagePath,
            ':has_offer'      => $hasOffer,
            ':offer_type'     => $offerType,
            ':offer_value'    => $offerValue
        ]);

        // Obtiene el ID del nuevo producto insertado
        $newId = (int)$pdo->lastInsertId();

        response_and_exit(true, 'Producto creado correctamente.', [
            'id' => $newId,
            'image_path' => $imagePath  // Devuelve la ruta de la imagen al frontend
        ]);
    }

// MANEJO DE EXCEPCIONES


} catch (PDOException $e) {
    // Errores específicos de la base de datos
    // Ejemplos: violación de restricciones UNIQUE, error de conexión, etc.
    http_response_code(500); // Error interno del servidor
    response_and_exit(false, 'Error en la base de datos: ' . $e->getMessage());
    
} catch (Exception $e) {
    // Errores generales de PHP
    // Ejemplos: errores en el manejo de archivos, memoria insuficiente, etc.
    http_response_code(500);
    response_and_exit(false, 'Error inesperado: ' . $e->getMessage());
}