INSERT INTO users (role_id, name, email, password_hash, phone, country, address, member_since, is_active, created_at, updated_at) VALUES
-- ADMINISTRADORES (role_id = 1)
(1, 'Admin1', 'admin1@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 1111 1111', 'México', 'Av. Insurgentes Sur #123, CDMX', '2023-01-15', TRUE, NOW(), NOW()),
(1, 'Admin2', 'admin2@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 2222 2222', 'México', 'Paseo de la Reforma #456, CDMX', '2023-02-20', TRUE, NOW(), NOW()),
(1, 'Admin3', 'admin3@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 3333 3333', 'México', 'Calle Liverpool #789, CDMX', '2023-03-10', TRUE, NOW(), NOW()),
(1, 'Admin4', 'admin4@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 4444 4444', 'México', 'Blvd. Manuel Ávila Camacho #101, CDMX', '2023-04-05', TRUE, NOW(), NOW()),

-- CLIENTES (role_id = 2)
(2, 'Cliente1', 'cliente1@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 5555 5555', 'México', 'Calle Madero #202, CDMX', '2023-01-20', TRUE, NOW(), NOW()),
(2, 'Cliente2', 'cliente2@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 6666 6666', 'México', 'Av. Revolución #303, CDMX', '2023-02-15', TRUE, NOW(), NOW()),
(2, 'Cliente3', 'cliente3@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 7777 7777', 'México', 'Calle Monterrey #404, CDMX', '2023-03-25', TRUE, NOW(), NOW()),
(2, 'Cliente4', 'cliente4@rockstore.com', '$2y$10$k8otaFZCqYoomij2h79nreOCL5L6K1sJ6cjILFxB7am5Z1LDRz94a', '+52 55 8888 8888', 'México', 'Av. Universidad #505, CDMX', '2023-04-30', TRUE, NOW(), NOW());