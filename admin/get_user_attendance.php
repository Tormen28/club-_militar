<?php
require_once '../includes/db_connect.php';

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Verificar que se haya proporcionado un ID de usuario
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de usuario no válido']);
    exit;
}

$userId = (int)$_GET['user_id'];
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Validar mes
if ($month < 0 || $month > 12) {
    $month = (int)date('m');
}

// Validar año
if ($year < 2000 || $year > 2100) {
    $year = (int)date('Y');
}

try {
    // Verificar conexión a la base de datos
    if (!isset($conn) || !$conn) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Construir la consulta base
    $query = "SELECT 
                a.id_asistencia, 
                a.fecha_hora_entrada, 
                a.fecha_hora_salida, 
                a.tipo_registro, 
                a.observaciones,
                u.nombre, 
                u.apellido, 
                u.cedula
              FROM asistencia a
              JOIN usuarios u ON a.id_usuario = u.id_usuario
              WHERE a.id_usuario = ?";
    
    $params = [$userId];
    $types = 'i';
    
    // Agregar filtro por mes si es válido
    if ($month >= 1 && $month <= 12) {
        $query .= " AND MONTH(a.fecha_hora_entrada) = ?";
        $params[] = $month;
        $types .= 'i';
    }
    
    // Agregar filtro por año
    $query .= " AND YEAR(a.fecha_hora_entrada) = ?";
    $params[] = $year;
    $types .= 'i';
    
    // Ordenar por fecha de entrada descendente
    $query .= " ORDER BY a.fecha_hora_entrada DESC";
    
    // Preparar la consulta
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    // Vincular parámetros dinámicamente
    $bindParams = [$types];
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    
    // Usar call_user_func_array para vincular parámetros
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    
    // Ejecutar la consulta
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    // Obtener resultados
    $result = $stmt->get_result();
    $attendance = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $attendance[] = [
                'id_asistencia' => $row['id_asistencia'],
                'fecha_hora_entrada' => $row['fecha_hora_entrada'],
                'fecha_hora_salida' => $row['fecha_hora_salida'],
                'tipo_registro' => $row['tipo_registro'],
                'observaciones' => $row['observaciones'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'cedula' => $row['cedula']
            ];
        }
    }
    
    // Cerrar la consulta
    $stmt->close();
    
    // Devolver los resultados
    echo json_encode($attendance);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error en el servidor: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

// Cerrar la conexión
if (isset($conn) && $conn) {
    $conn->close();
}
?>
