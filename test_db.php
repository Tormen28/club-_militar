<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Intentando incluir db_connect.php...<br>";
require_once 'includes/db_connect.php';
echo "db_connect.php incluido.<br>";

if ($conn) {
    echo "Conexión a la base de datos exitosa.<br>";
    // Puedes añadir más pruebas aquí si la conexión es exitosa
    // Por ejemplo, intentar una consulta simple:
    // $test_query = "SELECT 1";
    // if ($conn->query($test_query)) {
    //     echo "Consulta de prueba exitosa.<br>";
    // } else {
    //     echo "Error en consulta de prueba: " . $conn->error . "<br>";
    // }

    // No cierres la conexión aquí si necesitas que persista para otras pruebas
    // $conn->close();
    // echo "Conexión cerrada.<br>";
} else {
    echo "La variable \$conn es nula o la conexión falló antes de asignarla.<br>";
}

echo "Fin del script de prueba.<br>";
?>