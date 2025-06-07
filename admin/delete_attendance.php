<?php
require_once '../includes/db_connect.php';
session_start();

// Verificar si es un administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Preparar la consulta SQL
    $stmt = $conn->prepare("DELETE FROM asistencia WHERE id_asistencia = ?");
    $stmt->bind_param("i", $id);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar el registro']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
}

$conn->close();
?>