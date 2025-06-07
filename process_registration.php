<?php
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $Profesion = $_POST['Profesion'];
    $telefono = $_POST['telefono'];
    $sexo = $_POST['sexo'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    // Basic validation (you should add more robust validation)
    if (empty($cedula) || empty($nombre)) {
        header("Location: register_user.php?error=empty_fields");
        exit();
    }

    // Check if user already exists
    $check_sql = "SELECT id_usuario FROM usuarios WHERE cedula = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $cedula);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header("Location: register_user.php?error=user_exists");
        exit();
    }

    // Insert new user into the database
    $insert_sql = "INSERT INTO usuarios (cedula, nombre, apellido, Profesion, telefono, sexo, direccion, fecha_nacimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssssss", $cedula, $nombre, $apellido, $Profesion, $telefono, $sexo, $direccion, $fecha_nacimiento);

    if ($insert_stmt->execute()) {
        header("Location: index.php?success=registered");
        exit();
    } else {
        header("Location: register_user.php?error=registration_failed");
        exit();
    }

    $insert_stmt->close();
    $check_stmt->close();
    $conn->close();
} else {
    header("Location: register_user.php");
    exit();
}
?>
