<?php
// TODO: Iniciar sesión y lógica de la página principal

// Start the session to access messages set by registrar_asistencia.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for messages and prepare for JavaScript alert
$alert_message = '';
if (isset($_SESSION['mensaje_exito'])) {
    $alert_message = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
} elseif (isset($_SESSION['mensaje_alerta'])) {
    $alert_message = $_SESSION['mensaje_alerta'];
    unset($_SESSION['mensaje_alerta']);
} elseif (isset($_SESSION['mensaje_error'])) {
    $alert_message = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Militar - Registro de Asistencia</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        main {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        section {
            max-width: 400px;
            width: 100%;
        }
        form {
            width: 100%;
        }
        .button-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        .button-container button {
            flex: 1 1 0;
            width: 100%;
        }
        .register-link {
            width: 100%;
            margin: 20px 0 0 0;
            box-sizing: border-box;
            display: block;
            text-align: center;
        }
        @media (max-width: 500px) {
            section {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Club Militar</h1>
        <nav>
            <a href="#">Área de Usuario</a>
            <a href="admin/index.php">Admin Login</a>
        </nav>
    </header>

    <main>
        <section>
            <h2 style="text-align:center;">Registrar Asistencia</h2>
            <p style="text-align:center;">Ingrese su Cédula para marcar entrada o salida</p>
            <form action="includes/registrar_asistencia.php" method="post" autocomplete="off">
                <div class="input-icon">
                    <i class="icon">👤</i>
                    <input type="text" id="cedula" name="cedula" placeholder="Ingrese su cédula" required pattern="^\d+$" title="Solo números">
                </div>
                <input type="hidden" id="fecha_hora_cliente" name="fecha_hora_cliente">

                <div class="button-container">
                    <button type="submit" name="action" value="entrada" class="entry-color">Marcar Entrada</button>
                    <button type="submit" name="action" value="salida" class="exit-color">Marcar Salida</button>
                </div>
            </form>
            <a href="register_user.php" class="register-link">👤 Registrar Nuevo Civil</a>
        </section>
    </main>
    <script>
        // Solo números para cédula
        document.getElementById('cedula').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Al enviar el formulario, poner la fecha y hora local en el campo oculto
        document.querySelector('form').addEventListener('submit', function(e) {
            const now = new Date();
            // Formato: YYYY-MM-DD HH:MM:SS
            const fechaHora = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0');
            document.getElementById('fecha_hora_cliente').value = fechaHora;
        });

        // Mostrar alertas si existen
        const alertMessage = "<?php echo $alert_message; ?>";
        if (alertMessage) {
            alert(alertMessage);
        }
    </script>
    <footer>
        <p>&copy; 2025 Club Militar. Todos los derechos reservados.</p>
    </footer>
</body>
</html>