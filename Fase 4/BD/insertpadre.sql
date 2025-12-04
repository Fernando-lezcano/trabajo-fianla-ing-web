INSERT INTO roles (name) VALUES
('admin'),
('cliente');
INSERT INTO categories (name, slug, description) VALUES
('Instrumentos', 'instrumentos', 'Instrumentos musicales de todo tipo'),
('Ropa de Hombre', 'ropa-hombre', 'Ropa y accesorios para hombre'),
('Ropa de Mujer', 'ropa-mujer', 'Ropa y accesorios para mujer');
INSERT INTO subcategories (category_id, name, slug, description) VALUES
(1, 'Cuerda', 'cuerda', 'Instrumentos de cuerda como guitarras y bajos'),
(1, 'Percusión', 'percusion', 'Instrumentos de percusión como baterías y tambores'),
(1, 'Viento', 'viento', 'Instrumentos de viento como saxofones y trompetas');
INSERT INTO subcategories (category_id, name, slug, description) VALUES
(2, 'Camisas', 'camisas-hombre', 'Camisas y camisetas para hombre'),
(2, 'Chalecos', 'chalecos', 'Chalecos estilo rock/metal'),
(2, 'Gorras', 'gorras', 'Gorras y sombreros rockeros');
INSERT INTO subcategories (category_id, name, slug, description) VALUES
(3, 'Camisas', 'camisas-mujer', 'Blusas y camisas para mujer'),
(3, 'Accesorios', 'accesorios-mujer', 'Accesorios y complementos'),
(3, 'Vestidos', 'vestidos', 'Vestidos casuales y de estilo rock');
