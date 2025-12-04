<?php
// backend/productos/catalogo.php
// Reutilizable para TODAS las páginas de catálogo

require_once __DIR__ . '/../../BD/conexion.php'; // Ajusta si tu conexión está en otra ruta

/**
 * Obtiene productos desde la BD según filtros opcionales.
 *
 * Opciones soportadas:
 *  - 'subcategory_slug' => string  (ej: 'chalecos', 'viento', 'cuerda', etc.)
 *  - 'only_offers'      => bool    (true = solo productos con oferta)
 *
 * @param array $options
 * @return array
 */
function obtenerProductos(array $options = []): array
{
    global $pdo; // conexión desde BD/conexion.php

    // Siempre empezamos con una condición verdadera
    $where  = ["1 = 1"];
    $params = [];

    // 🔹 Filtrar por subcategoría (slug), ej: 'chalecos', 'viento', 'cuerda'
    if (!empty($options['subcategory_slug'])) {
        $where[] = "s.slug = :subcategory_slug";
        $params[':subcategory_slug'] = $options['subcategory_slug'];
    }

    // 🔹 Solo productos con oferta (NO filtra por fechas, solo por has_offer = 1)
    if (!empty($options['only_offers'])) {
        $where[] = "p.has_offer = 1";
    }

    // 🔹 Solo productos destacados (para "Lo más vendido" del home)
    if (!empty($options['only_featured'])) {
        $where[] = "p.is_featured = 1";
    }

    // Consulta principal
    $sql = "
        SELECT 
            p.id,
            p.code,
            p.name,
            p.short_description,
            p.price,               -- columna REAL de tu tabla
            p.stock,
            p.status,
            p.image_path,
            p.has_offer,
            p.offer_type,
            p.offer_value,
            p.offer_start,
            p.offer_end,
            p.is_featured,
            s.name AS subcategory_name,
            s.slug AS subcategory_slug,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        INNER JOIN subcategories s ON p.subcategory_id = s.id
        INNER JOIN categories c    ON s.category_id   = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Renderiza el GRID de productos con el HTML de las cards.
 * Usa las mismas clases que ya tienes en tus .html:
 *  - .products-grid
 *  - .product-card
 *  - .favorite-btn
 *  - .add-to-cart-btn
 */
function renderProductosGrid(array $options = []): void
{
    $productos = obtenerProductos($options);

    if (empty($productos)) {
        echo '<p class="text-muted">No hay productos disponibles en esta sección.</p>';
        return;
    }

    // 👇 OJO: ya NO abrimos <div class="products-grid"> aquí

    foreach ($productos as $p) {
        $precioOriginal   = (float) $p['price'];
        $precioFinal      = $precioOriginal;
        $tieneOferta      = (int)$p['has_offer'] === 1;

        // Calcular precio final según tipo de oferta
        if ($tieneOferta && !empty($p['offer_type']) && $p['offer_value'] !== null) {
            if ($p['offer_type'] === 'porcentaje') {
                $precioFinal = $precioOriginal * (1 - ((float)$p['offer_value'] / 100));
            } elseif ($p['offer_type'] === 'precio_fijo') {
                $precioFinal = (float)$p['offer_value'];
            }
        }

        // 🔥 Corregimos la ruta de la imagen
        $rawPath = trim((string)($p['image_path'] ?? ''));

        if ($rawPath !== '') {
            // En BD tienes algo como: /productos/bateria_inferno.png
            // Desde /html necesitamos: ../productos/bateria_inferno.png
            $img = '../' . ltrim($rawPath, '/');
        } else {
            // Placeholder por si no hay imagen
            $img = '../img2/placeholder.png';
        }

        $img    = htmlspecialchars($img);
        $nombre = htmlspecialchars($p['name']);

        $precioOriginalFmt = number_format($precioOriginal, 2);
        $precioFinalFmt    = number_format($precioFinal, 2);

        echo '<div class="product-card">';
        echo '  <button class="favorite-btn" onclick="toggleFavorite(this)" aria-label="Agregar a favoritos">';
        echo '      <i class="far fa-heart"></i>';
        echo '  </button>';
        echo '  <div class="product-image">';
        echo '      <img src="'. $img .'" alt="'. $nombre .'" class="product-image-img">';
        echo '  </div>';
        echo '  <div class="product-info">';
        echo '      <div class="product-name">'. $nombre .'</div>';
        echo '      <div class="product-price">';
        if ($tieneOferta) {
            echo '          <span class="price-original">$'. $precioOriginalFmt .'</span>';
            echo '          <span class="price-current">$'. $precioFinalFmt .'</span>';
        } else {
            echo '          <span class="price-current">$'. $precioOriginalFmt .'</span>';
        }
        echo '      </div>';

        // Botón de carrito conectado a cart.js
        echo '      <button class="add-to-cart-btn" ';
        echo '              data-product-id="'. (int)$p['id'] .'" ';
        echo '              onclick="addToCart(this)">';
        echo '          <i class="fas fa-shopping-cart"></i>';
        echo '          Agregar al carrito';
        echo '      </button>';

        echo '  </div>';
        echo '</div>';
    }

    // 👇 Ya NO cerramos </div> de products-grid aquí
}