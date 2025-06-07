<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Verificar que se recibió el ID de asistencia
if (!isset($_POST['id_asistencia']) || !is_numeric($_POST['id_asistencia'])) {
    echo json_encode(['success' => false, 'message' => 'ID de asistencia no válido']);
    exit;
}

$id_asistencia = (int)$_POST['id_asistencia'];
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

try {
    // Preparar la consulta SQL
    $sql = "UPDATE asistencia SET observaciones = ? WHERE id_asistencia = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    // Vincular parámetros
    $stmt->bind_param('si', $observaciones, $id_asistencia);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log('Error en update_observations.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
