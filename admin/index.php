<?php
session_start();
require_once '../includes/db_connect.php';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $conn->real_escape_string($_POST['admin_usuario']);
    $password = $_POST['admin_password'];

    $stmt = $conn->prepare('SELECT id_admin, usuario, contrasena_hash FROM administradores WHERE usuario = ?');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['contrasena_hash'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            header('Location: dashboard.php');
            exit;
        } else {
            $login_error = 'Credenciales incorrectas';
        }
    } else {
        $login_error = 'Usuario no encontrado';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Club Militar</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Centrar el contenido del main vertical y horizontalmente */
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
        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
            align-items: stretch;
        }
        .form-actions button,
        .form-actions .register-link {
            width: 100%;
            margin: 0;
            box-sizing: border-box;
        }
        .error-message {
            margin-bottom: 15px;
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
            <a href="../index.php">Área de Asistencia</a>
            <a href="index.php">Admin Login</a>
        </nav>
    </header>

    <main>
        <section>
            <h2 style="text-align:center;">Admin Login</h2>
            <?php if (!empty($login_error)): ?>
                <p class="error-message"><?= htmlspecialchars($login_error) ?></p>
            <?php endif; ?>
            <form action="index.php" method="post" autocomplete="off">
                <label for="admin_usuario">Usuario</label>
                <div class="input-icon">
                    <i class="icon">👤</i>
                    <input type="text" id="admin_usuario" name="admin_usuario" placeholder="admin" required>
                </div>

                <label for="admin_password">Contraseña</label>
                <div class="input-icon">
                    <i class="icon">🔑</i>
                    <input type="password" id="admin_password" name="admin_password" placeholder="********" required>
                </div>

                <div class="form-actions">
                    <button type="submit">→ Iniciar Sesión</button>
                    <a href="../index.php" class="register-link">🏠 Asistencia</a>
                </div>
            </form>
        </section>
    </main>

   <footer>
        <p>&copy; 2025 Club Militar. Todos los derechos reservados.</p>
    </footer>
</body>
</html>