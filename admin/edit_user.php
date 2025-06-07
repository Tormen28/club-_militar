<?php
require_once '../includes/db_connect.php';
session_start();

// Verificar si es un administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ['success' => false, 'message' => ''];
    
    // Obtener los datos del formulario
    $id_usuario = $_POST['id_usuario'];
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $profesion = $_POST['profesion'];
    $telefono = $_POST['telefono'];
    $sexo = $_POST['sexo'];
    $direccion = $_POST['direccion'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    
    // Preparar la consulta SQL
    $stmt = $conn->prepare("UPDATE usuarios SET cedula = ?, nombre = ?, apellido = ?, 
        profesion = ?, telefono = ?, sexo = ?, direccion = ?, fecha_nacimiento = ? 
        WHERE id_usuario = ?");
    
    $stmt->bind_param("ssssssssi", $cedula, $nombre, $apellido, $profesion, 
        $telefono, $sexo, $direccion, $fecha_nacimiento, $id_usuario);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Usuario actualizado exitosamente';
    } else {
        $response['message'] = 'Error al actualizar el usuario: ' . $conn->error;
    }
    
    $stmt->close();
    echo json_encode($response);
    exit();
}

// Si se solicita obtener los datos del usuario
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode($usuario);
    exit();
}

$conn->close();
?>