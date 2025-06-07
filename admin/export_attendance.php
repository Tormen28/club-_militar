<?php
require_once '../includes/db_connect.php';
session_start();

// Verificar si es un administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    header("Location: index.php");
    exit();
}

// Configurar cabeceras para descargar como archivo Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=asistencia_' . date('Y-m-d') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

// Obtener el título según el filtro
$titulo = 'TODOS LOS REGISTROS';
$subtitulo = '';
$filtro_fecha = '';

if(isset($_GET['filterDate']) && !empty($_GET['filterDate'])) {
    $fecha = DateTime::createFromFormat('Y-m-d', $_GET['filterDate']);
    if($fecha) {
        $filtro_fecha = $conn->real_escape_string($fecha->format('Y-m-d'));
        $titulo = 'FILTRADO POR FECHA: ' . $fecha->format('d/m/Y');
    }
} elseif(isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $query_user = "SELECT CONCAT(nombre, ' ', apellido) as nombre_completo, cedula FROM usuarios WHERE id_usuario = $user_id";
    $result_user = $conn->query($query_user);
    if($result_user && $result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        $titulo = strtoupper($user['nombre_completo']);
        $subtitulo = 'CÉDULA: ' . $user['cedula'];
    }
}

// Construir la consulta SQL
$whereClause = '';
if(!empty($filtro_fecha)) {
    $whereClause = " WHERE DATE(a.fecha_hora_entrada) = '$filtro_fecha'";
} elseif(isset($user_id)) {
    $whereClause = " WHERE a.id_usuario = $user_id";
}

$query = "SELECT 
    u.cedula, 
    u.nombre, 
    u.apellido, 
    CASE 
        WHEN a.tipo_registro = 'entrada' THEN 'Entrada' 
        ELSE 'Salida' 
    END as tipo_registro,
    DATE_FORMAT(a.fecha_hora_entrada, '%d/%m/%Y %H:%i:%s') as fecha_hora_entrada, 
    IFNULL(DATE_FORMAT(a.fecha_hora_salida, '%d/%m/%Y %H:%i:%s'), '') as fecha_hora_salida,
    IFNULL(a.observaciones, '') as observaciones
    FROM asistencia a
    JOIN usuarios u ON a.id_usuario = u.id_usuario
    $whereClause
    ORDER BY a.fecha_hora_entrada DESC";

$result = $conn->query($query);

// Crear el contenido HTML para Excel
// Incluir el logo solo si existe
$logo_path = __DIR__ . '/../logo.jpg';
$logo_html = '';

if (file_exists($logo_path) && is_file($logo_path)) {
    $mime_type = mime_content_type($logo_path);
    if (strpos($mime_type, 'image/') === 0) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        $logo_html = '<div style="width: 80px; height: 80px; margin: 0 auto 5px auto; overflow: hidden; display: flex; align-items: center; justify-content: center;">';
        $logo_html .= '<img src="data:' . $mime_type . ';base64,' . $logo_data . '" ';
        $logo_html .= 'style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;" ';
        $logo_html .= 'alt="Club Militar">';
        $logo_html .= '</div>';
    }
}

$html = '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 15px; }
        .logo-container { margin-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #1F4E79; margin: 10px 0 5px 0; }
        .subtitle { font-size: 14px; color: #555555; margin: 5px 0; }
        .date { font-size: 12px; color: #777777; margin: 5px 0 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #1F4E79; color: white; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #dddddd; }
        td { padding: 6px; border: 1px solid #dddddd; text-align: left; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">CLUB MILITAR</div>
        <div class="subtitle">HISTORIAL DE ASISTENCIA</div>
        <div class="subtitle">' . $titulo . '</div>';

if(!empty($subtitulo)) {
    $html .= '<div class="subtitle">' . $subtitulo . '</div>';
}

$html .= '<div class="date">Generado el: ' . date('d/m/Y H:i:s') . '</div>
    </div>
    
    <table border="1">
        <tr>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Tipo</th>
            <th>Fecha Hora Entrada</th>
            <th>Fecha Hora Salida</th>
            <th>Observaciones</th>
        </tr>';

// Agregar filas de datos
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['cedula']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['nombre']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['apellido']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['tipo_registro']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['fecha_hora_entrada']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['fecha_hora_salida']) . '</td>';
        $html .= '<td>' . str_replace("\n", ' ', htmlspecialchars($row['observaciones'])) . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="7" style="text-align: center;">No hay registros de asistencia</td></tr>';
}

$html .= '</table>
</body>
</html>';

// Imprimir el contenido
echo $html;

// Cerrar la conexión a la BD
$conn->close();
exit();
?>