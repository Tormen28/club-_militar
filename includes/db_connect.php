<?php
$servername = "localhost";
$username = "root"; // Reemplaza con tu nombre de usuario de MySQL
$password = ""; // Reemplaza con tu contraseña de MySQL
$dbname = "club_militar_db";

// Crear conexión al servidor MySQL (sin especificar la base de datos inicialmente)
$conn = new mysqli($servername, $username, $password);

// Verificar conexión al servidor
if ($conn->connect_error) {
    die("Conexión al servidor fallida: " . $conn->connect_error);
}

// Crear base de datos si no existe
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    // Base de datos creada o ya existe
} else {
    echo "Error al crear la base de datos: " . $conn->error;
    // Considerar si detener la ejecución o continuar con un error
}

// Seleccionar la base de datos
$conn->select_db($dbname);

// SQL para crear la tabla administradores si no existe
$sql_create_admin_table = "CREATE TABLE IF NOT EXISTS administradores (
    id_admin INT(11) NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_admin)
)";

// SQL para crear la tabla usuarios si no existe
$sql_create_usuarios_table = "CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT(11) NOT NULL AUTO_INCREMENT,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    profesion VARCHAR(255) DEFAULT NULL,
    telefono VARCHAR(50) DEFAULT NULL,
    sexo VARCHAR(10) DEFAULT NULL,
    direccion TEXT DEFAULT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario)
)";

// SQL para crear la tabla asistencia si no existe
$sql_create_asistencia_table = "CREATE TABLE IF NOT EXISTS asistencia (
    id_asistencia INT(11) NOT NULL AUTO_INCREMENT,
    id_usuario INT(11) NOT NULL,
    fecha_hora_entrada DATETIME DEFAULT NULL,
    fecha_hora_salida DATETIME DEFAULT NULL,
    tipo_registro ENUM('entrada','salida') NOT NULL,
    PRIMARY KEY (id_asistencia),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
)";

// Ejecutar consultas para crear tablas
if ($conn->query($sql_create_admin_table) === TRUE) {
    // Tabla administradores creada o ya existe
} else {
    echo "Error al crear la tabla administradores: " . $conn->error;
}

if ($conn->query($sql_create_usuarios_table) === TRUE) {
    // Tabla usuarios creada o ya existe
} else {
    echo "Error al crear la tabla usuarios: " . $conn->error;
}

if ($conn->query($sql_create_asistencia_table) === TRUE) {
    // Tabla asistencia creada o ya existe
} else {
    echo "Error al crear la tabla asistencia: " . $conn->error;
}

// Verificar si el administrador por defecto existe y si no, insertarlo
$admin_username = 'admin';
$admin_password = 'admin'; // Contraseña por defecto, ¡CAMBIAR EN PRODUCCIÓN!
$admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

$sql_check_admin = "SELECT id_admin FROM administradores WHERE usuario = '$admin_username'";
$result_check_admin = $conn->query($sql_check_admin);

if ($result_check_admin->num_rows == 0) {
    // No existe el administrador, insertarlo
    $sql_insert_admin = "INSERT INTO administradores (usuario, contrasena_hash) VALUES ('$admin_username', '$admin_password_hash')";
    if ($conn->query($sql_insert_admin) === TRUE) {
        // Administrador por defecto insertado
    } else {
        echo "Error al insertar administrador por defecto: " . $conn->error;
    }
}

// La conexión a la base de datos está ahora en la variable $conn
// No cierres la conexión aquí si otros scripts la van a usar.
// $conn->close(); // Cierra la conexión solo si este es el último script en usarla
?>