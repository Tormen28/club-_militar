<?php
require_once 'db_connect.php'; // Conexión a la BD y creación de tablas

// Start the session to access messages set by registrar_asistencia.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cedula']) && isset($_POST['action'])) {
    $cedula = trim($_POST['cedula']);
    $accion = $_POST['action']; // 'entrada' o 'salida'

    if (empty($cedula)) {
        $_SESSION['mensaje_error'] = "El número de cédula no puede estar vacío.";
        header("Location: ../index.php");
        exit();
    }

    // Validar que la acción sea 'entrada' o 'salida'
    if ($accion !== 'entrada' && $accion !== 'salida') {
        $_SESSION['mensaje_error'] = "Acción no válida.";
        header("Location: ../index.php");
        exit();
    }

    // Verificar si el usuario ya existe por cédula
    $stmt_check_usuario = $conn->prepare("SELECT id_usuario FROM usuarios WHERE cedula = ?");
    $stmt_check_usuario->bind_param("s", $cedula);
    $stmt_check_usuario->execute();
    $result_usuario = $stmt_check_usuario->get_result();
    $id_usuario = null;

    if ($result_usuario->num_rows > 0) {
        $usuario_db = $result_usuario->fetch_assoc();
        $id_usuario = $usuario_db['id_usuario'];

        // Obtener la fecha y hora del cliente si está disponible
        $current_datetime = isset($_POST['fecha_hora_cliente']) && !empty($_POST['fecha_hora_cliente'])
            ? $_POST['fecha_hora_cliente']
            : date('Y-m-d H:i:s');

        $mensaje_exito = "";
        $mensaje_error_logica = "";

        // Verificar el último registro para este usuario
        $stmt_last_asistencia = $conn->prepare("SELECT id_asistencia, tipo_registro, fecha_hora_entrada, fecha_hora_salida FROM asistencia WHERE id_usuario = ? ORDER BY id_asistencia DESC LIMIT 1");
        $stmt_last_asistencia->bind_param("i", $id_usuario);
        $stmt_last_asistencia->execute();
        $result_last_asistencia = $stmt_last_asistencia->get_result();
        $ultima_asistencia = $result_last_asistencia->fetch_assoc();
        $stmt_last_asistencia->close();

        if ($accion == 'entrada') {
            if ($ultima_asistencia && $ultima_asistencia['tipo_registro'] == 'entrada' && empty($ultima_asistencia['fecha_hora_salida'])) {
                $mensaje_error_logica = "Ya tiene una entrada registrada sin una salida previa.";
            } else {
                // Registrar nueva entrada
                $stmt_registro = $conn->prepare("INSERT INTO asistencia (id_usuario, fecha_hora_entrada, tipo_registro) VALUES (?, ?, ?)");
                $stmt_registro->bind_param("iss", $id_usuario, $current_datetime, $accion);
                if ($stmt_registro->execute()) {
                    $mensaje_exito = "Entrada registrada correctamente para la cédula: " . htmlspecialchars($cedula);
                } else {
                    $_SESSION['mensaje_error'] = "Error al registrar la entrada: " . $stmt_registro->error;
                }
                $stmt_registro->close();
            }
        } elseif ($accion == 'salida') {
            if (!$ultima_asistencia || $ultima_asistencia['tipo_registro'] == 'salida' || empty($ultima_asistencia['fecha_hora_entrada']) || !empty($ultima_asistencia['fecha_hora_salida'])) {
                $mensaje_error_logica = "Debe registrar una entrada antes de una salida, o ya tiene una salida registrada para la última entrada.";
            } else {
                // Actualizar el registro de entrada existente con la hora de salida
                $stmt_registro = $conn->prepare("UPDATE asistencia SET fecha_hora_salida = ?, tipo_registro = ? WHERE id_asistencia = ? AND id_usuario = ?");
                $stmt_registro->bind_param("ssii", $current_datetime, $accion, $ultima_asistencia['id_asistencia'], $id_usuario);
                if ($stmt_registro->execute()) {
                    $mensaje_exito = "Salida registrada correctamente para la cédula: " . htmlspecialchars($cedula);
                } else {
                    $_SESSION['mensaje_error'] = "Error al registrar la salida: " . $stmt_registro->error;
                }
                $stmt_registro->close();
            }
        }

        if (!empty($mensaje_error_logica)){
            $_SESSION['mensaje_alerta'] = $mensaje_error_logica;
        } else if (!empty($mensaje_exito)){
            $_SESSION['mensaje_exito'] = $mensaje_exito;
        }

    } else {
        // Si el usuario NO existe, mostrar mensaje y redirigir
        $_SESSION['mensaje_alerta'] = "La cédula " . htmlspecialchars($cedula) . " no está registrada. Por favor, regístrese primero.";
    }

    $stmt_check_usuario->close();
    $conn->close();
    header("Location: ../index.php");
    exit();

} else {
    $_SESSION['mensaje_error'] = "Solicitud no válida.";
    header("Location: ../index.php");
    exit();
}
?>