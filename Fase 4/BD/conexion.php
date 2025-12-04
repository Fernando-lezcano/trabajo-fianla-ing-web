<?php
// BD/conexion.php
// Archivo de configuración y conexión a la base de datos MySQL usando PDO
// Este archivo debe ser incluido en todos los scripts que necesiten acceso a la BD


// 1. CONFIGURACIÓN DE CREDENCIALES DE ACCESO


// Configuración de conexión a MySQL/MariaDB
$db_host = 'localhost';      // Dirección del servidor de base de datos
$db_name = 'rockstore';      // Nombre de la base de datos a la que conectarse
$db_user = 'root';           // Usuario de la base de datos
$db_pass = '';               // Contraseña del usuario (vacia en desarrollo local)


// 2. CONSTRUCCIÓN DEL DSN (Data Source Name)

// DSN: Cadena de conexión que PDO usa para identificar la base de datos
// Formato: "mysql:host=HOST;dbname=NOMBRE_BD;charset=CODIFICACION"
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

// Explicación de charset=utf8mb4:
// - utf8mb4 soporta caracteres Unicode completos (incluyendo emojis)
// - Es superior a utf8 estándar que solo soporta BMP (Basic Multilingual Plane)
// - Necesario para almacenar caracteres como emojis  o ciertos ideogramas


// 3. OPCIONES DE CONFIGURACIÓN DE PDO

// Array de opciones para configurar el comportamiento de PDO
$options = [
    // ATTR_ERRMODE: Configura cómo PDO reporta errores
    // PDO::ERRMODE_EXCEPTION: Lanza excepciones (PDOException) cuando ocurren errores
    // Esto permite usar try-catch para manejo elegante de errores
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // ATTR_DEFAULT_FETCH_MODE: Define el modo de recuperación por defecto
    // PDO::FETCH_ASSOC: Devuelve arrays asociativos (nombre_columna => valor)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // ATTR_EMULATE_PREPARES: Controla si PDO emula prepared statements
    // false: Usa prepared statements nativos del driver MySQL (más seguro)
    // true: PDO emula los prepared statements (menos seguro, pero más compatible)
    PDO::ATTR_EMULATE_PREPARES   => false,
    
];


// 4. ESTABLECIMIENTO DE LA CONEXIÓN


try {
    // Crea una nueva instancia de PDO (objeto de conexión a la base de datos)
    // Parámetros: DSN, usuario, contraseña, opciones de configuración
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Si la conexión es exitosa, $pdo estará disponible para todas las consultas
    // Este objeto será reutilizado por todos los scripts que incluyan este archivo
    
} catch (PDOException $e) {

    // 5. MANEJO DE ERRORES DE CONEXIÓN
    
    
    // PDOException se lanza cuando hay problemas de conexión
    // Posibles causas:
    // 1. Servidor MySQL no está ejecutándose
    // 2. Credenciales incorrectas (usuario/contraseña)
    // 3. Base de datos no existe
    // 4. Problemas de red o firewall
    
    // IMPORTANTE: En producción, NO muestres el mensaje de error completo
    // Esto podría exponer información sensible (credenciales, estructura de BD)
    
    // Para desarrollo: Muestra el error completo para debugging
    die('Error de conexión a la base de datos: ' . $e->getMessage());
    
    // Para producción: Usa un mensaje genérico y registra el error
    // error_log('Error de conexión BD: ' . $e->getMessage());
    // die('Error interno del servidor. Contacte al administrador.');
}

// VARIABLE $pdo DISPONIBLE PARA USO GLOBAL

// Una vez conectado exitosamente, el objeto $pdo está disponible
// y puede ser usado en cualquier script que incluya este archivo
// Ejemplo: $pdo->query("SELECT * FROM productos");