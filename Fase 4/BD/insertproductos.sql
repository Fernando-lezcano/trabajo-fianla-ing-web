INSERT INTO products (
  category_id, subcategory_id, code, name, short_description, 
  price, stock, status, image_path,
  has_offer, offer_type, offer_value, is_featured
) VALUES
-- 🎸 CUERDA (ID = 1)
(1, 1, 'PROD001', 'Guitarra Eléctrica Shadow', 'Guitarra de seis cuerdas estilo rock.', 
 650.00, 12, 'en_stock', '/productos/guitarra_shadow.png',
 1, 'porcentaje', 15.00, 1),

(1, 1, 'PROD002', 'Bajo Thunderbolt', 'Bajo eléctrico de cuatro cuerdas.', 
 720.00, 7, 'stock_bajo', '/productos/bajo_thunderbolt.png',
 0, NULL, NULL, 0),
-- 🥁 PERCUSIÓN (ID = 2)
(1, 2, 'PROD003', 'Batería Inferno', 'Set de batería con doble bombo.', 
 1200.00, 5, 'stock_bajo', '/productos/bateria_inferno.png',
 1, 'precio_fijo', 999.00, 1),

(1, 2, 'PROD004', 'Cajón Flamenco Pro', 'Cajón de madera profesional.', 
 180.00, 20, 'en_stock', '/productos/cajon_flamenco.png',
 0, NULL, NULL, 0),

-- 🎷 VIENTO (ID = 3)
(1, 3, 'PROD005', 'Saxofón Alto Gold', 'Saxofón alto acabado dorado.', 
 900.00, 3, 'stock_bajo', '/productos/saxofon_golden.png',
 1, 'porcentaje', 10.00, 0),

(1, 3, 'PROD006', 'Trompeta Bravo', 'Trompeta clásica para estudiantes.', 
 350.00, 25, 'en_stock', '/productos/trompeta_bravo.png',
 0, NULL, NULL, 0),

-- 👕 CAMISAS HOMBRE (ID = 4)
(2, 4, 'PROD007', 'Camisa Slayer Negra', 'Camisa de algodón estampada.', 
 30.00, 18, 'en_stock', '/productos/camisa_slayer.jpg',
 1, 'porcentaje', 20.00, 1),

(2, 4, 'PROD008', 'Camisa Metallica Roja', 'Camisa casual con logo bordado.', 
 32.00, 10, 'en_stock', '/productos/camisa_metallica.webp',
 0, NULL, NULL, 0),

-- 🧥 CHALECOS HOMBRE (ID = 5)
(2, 5, 'PROD009', 'Chaleco Rocker Classic', 'Chaleco negro estilo rock.', 
 55.00, 8, 'stock_bajo', '/productos/chaleco_rocker.webp',
 0, NULL, NULL, 0),

(2, 5, 'PROD010', 'Chaleco Leather Brutal', 'Chaleco de cuero sintético.', 
 75.00, 4, 'stock_bajo', '/productos/chaleco_brutal.png',
 1, 'precio_fijo', 60.00, 0),

-- 🧢 GORRAS (ID = 6)
(2, 6, 'PROD011', 'Gorra Iron Maiden', 'Gorra negra con logo frontal.', 
 25.00, 30, 'en_stock', '/productos/gorra_ironmaiden.jpg',
 0, NULL, NULL, 1),

(2, 6, 'PROD012', 'Gorra Linkin Park', 'Gorra ajustable edición limitada.', 
 28.00, 15, 'en_stock', '/productos/gorra_linkinpark.jpg',
 1, 'porcentaje', 10.00, 0),

-- 👚 CAMISAS MUJER (ID = 7)
(3, 7, 'PROD013', 'Camisa Mujer Gothic Rose', 'Blusa negra con diseño de rosas.', 
 35.00, 22, 'en_stock', '/productos/camisa_gothicrose.jpg',
 0, NULL, NULL, 1),

(3, 7, 'PROD014', 'Camisa Mujer Skull Art', 'Camisa de calavera estilo artístico.', 
 37.00, 12, 'en_stock', '/productos/camisa_skullart.jpg',
 1, 'precio_fijo', 30.00, 0),

-- 💍 ACCESORIOS MUJER (ID = 8)
(3, 8, 'PROD015', 'Pulsera Rock Deluxe', 'Pulsera metálica estilo rock.', 
 18.00, 40, 'en_stock', '/productos/pulsera_rock.webp',
 0, NULL, NULL, 0),

(3, 8, 'PROD016', 'Collar Gothic Heart', 'Collar negro con corazón gótico.', 
 22.00, 25, 'en_stock', '/productos/collar_gothic.jpg',
 1, 'porcentaje', 15.00, 1),

-- 👗 VESTIDOS (ID = 9)
(3, 9, 'PROD017', 'Vestido Rock Fever', 'Vestido negro con detalles metálicos.', 
 80.00, 10, 'en_stock', '/productos/vestido_rockfever.png',
 0, NULL, NULL, 1),

(3, 9, 'PROD018', 'Vestido Dark Queen', 'Vestido elegante estilo gótico.', 
 95.00, 6, 'stock_bajo', '/productos/vestido_darkqueen.png',
 1, 'precio_fijo', 80.00, 0);
