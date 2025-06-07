<?php
require_once '../includes/db_connect.php';
session_start();

// Verificar si es un administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    header("Location: index.php");
    exit();
}

// Obtener los datos de la base de datos
$query_usuarios = "SELECT 
    cedula, 
    nombre, 
    apellido, 
    profesion, 
    telefono, 
    sexo, 
    direccion, 
    DATE_FORMAT(fecha_nacimiento, '%d/%m/%Y') as fecha_nacimiento, 
    DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i:%s') as fecha_registro 
    FROM usuarios 
    ORDER BY fecha_registro DESC";

$result_usuarios = $conn->query($query_usuarios);

// Construir el contenido HTML primero
$html = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 15px; }
        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 10px; }
        th { background-color: #1F4E79; color: white; font-weight: bold; text-align: center; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; font-size: 12px; }
        .title { font-size: 18px; font-weight: bold; margin: 10px 0 5px 0; color: #1F4E79; }
        .subtitle { font-size: 16px; font-weight: bold; margin: 5px 0; color: #555555; }
        .date { font-size: 12px; color: #777777; margin: 5px 0 15px 0; text-align: right; }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <div class="title">CLUB MILITAR</div>
        <div class="subtitle">LISTA DE REGISTRO DE USUARIOS</div>
        <div class="date">Generado el: ' . date('d/m/Y H:i:s') . '</div>
    </div>
    
    <table border="1">
        <col width="120">
        <col width="150">
        <col width="150">
        <col width="100">
        <col width="100">
        <col width="80">
        <col width="200">
        <col width="120">
        <col width="150">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Profesión</th>
                <th>Teléfono</th>
                <th>Sexo</th>
                <th>Dirección</th>
                <th>Fecha Nacimiento</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody>';

// Agregar filas de datos
if ($result_usuarios && $result_usuarios->num_rows > 0) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['cedula']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['nombre']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['apellido']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['profesion']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['telefono']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['sexo']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['direccion']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['fecha_nacimiento']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['fecha_registro']) . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="9" style="text-align: center;">No hay usuarios registrados</td></tr>';
}

// Cerrar el HTML
$html .= '
        </tbody>
    </table>
</body>
</html>';

// Configurar cabeceras para la descarga del archivo Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="lista_de_registro_' . date('Ymd') . '.xls"');
header('Cache-Control: max-age=0');

// Enviar el contenido
echo $html;

// Cerrar la conexión a la BD
$conn->close();
exit();