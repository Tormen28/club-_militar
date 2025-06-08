<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Personal Civil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>I.A.C.F.A</h1>
        <nav>
            <a href="index.php">Área de Usuario</a>
            <a href="../club-militar-app-master/admin/index.php">Admin Login</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Registro Civil</h2>
            <p>Ingrese los datos para registrar un nuevo civil</p>

            <?php
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                if ($error == 'empty_fields') {
                    echo '<p class="error-message">Por favor, complete todos los campos requeridos.</p>';
                } elseif ($error == 'user_exists') {
                    echo '<p class="error-message">Error: El usuario con esta cédula ya está registrado.</p>';
                } elseif ($error == 'registration_failed') {
                    echo '<p class="error-message">Error: No se pudo completar el registro. Intente de nuevo.</p>';
                }
            } elseif (isset($_GET['success']) && $_GET['success'] == 'registered') {
                echo '<p class="success-message">¡Registro exitoso! El usuario ha sido registrado.</p>';
            }
            ?>

            <form action="process_registration.php" method="post">
    <label for="nombre_completo">Nombres</label>
    <div class="input-icon">
        <i class="icon">👤</i>
        <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan" required
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios">
    </div>

    <label for="apellido">Apellidos</label>
    <div class="input-icon">
        <i class="icon">👤</i>
        <input type="text" id="apellido" name="apellido" placeholder="Ej: Pérez" required
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios">
    </div>

    <label for="cedula">Cédula</label>
    <div class="input-icon">
        <i class="icon">💳</i>
        <input type="text" id="cedula" name="cedula" placeholder="Ej: 30123456" required
            pattern="^\d+$" title="Solo números">
    </div>

    <label for="Profesion">Profesión:</label>
    <div class="input-icon">
        <i class="icon">🎖️</i>
        <input type="text" id="Profesion" name="Profesion" placeholder="Ej: vigilante" required
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" title="Solo letras y espacios">
    </div>

    <label for="sexo">Sexo</label>
    <div class="input-icon">
        <i class="icon">🚻</i>
        <select id="sexo" name="sexo">
            <option value="">Seleccione el sexo</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
        </select>
    </div>

    <label for="direccion">Dirección</label>
    <div class="input-icon">
        <i class="icon">🏠</i>
        <input type="text" id="direccion" name="direccion" placeholder="Ej: Av. Principal, Casa #123">
    </div>

    <label for="telefono">Número de Teléfono</label>
    <div class="input-icon">
        <i class="icon">📞</i>
        <input type="text" id="telefono" name="telefono" placeholder="Ej: 04121234567" required
            pattern="^\d+$" title="Solo números">
    </div>

    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
    <div class="input-icon">
        <i class="icon">📅</i>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="dd/mm/aaaa">
    </div>

    <button type="submit">Registrar Usuario</button>
</form>

<script>
// Solo números para cédula y teléfono
['cedula', 'telefono'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '');
    });
});
// Solo letras y espacios para nombre, apellido y profesión
['nombre', 'apellido', 'Profesion'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', function(e) {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });
});
</script>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 I.A.C.F.A. Todos los derechos reservados.</p>
    </footer>
</body>
</html>