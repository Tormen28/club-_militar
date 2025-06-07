<?php
require_once '../includes/db_connect.php';
session_start();

// Verificación corregida
if (!isset($_SESSION['admin_id']) || $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    header("Location: index.php");
    exit();
}

$admin_usuario = $_SESSION['admin_usuario'];

// Determine which view to show (default to attendance)
$view = isset($_GET['view']) ? $_GET['view'] : 'attendance';

$result_registros = null;
$result_usuarios = null;

if ($view === 'attendance') {
    $whereClause = '';
    if(isset($_GET['filterDate']) && !empty($_GET['filterDate'])) {
        $fecha = DateTime::createFromFormat('Y-m-d', $_GET['filterDate']);
        if($fecha) {
            $filterDate = $conn->real_escape_string($fecha->format('Y-m-d'));
            $whereClause = " WHERE DATE(a.fecha_hora_entrada) = '{$filterDate}'";
        }
    }
    
    $query_registros = "SELECT a.id_asistencia, u.cedula, u.nombre, u.apellido, 
        u.profesion, u.telefono, u.sexo, u.direccion, u.fecha_nacimiento, 
        a.tipo_registro, a.fecha_hora_entrada, a.fecha_hora_salida, a.observaciones
        FROM asistencia a
        JOIN usuarios u ON a.id_usuario = u.id_usuario
        $whereClause
        ORDER BY a.fecha_hora_entrada DESC";
    $result_registros = $conn->query($query_registros);
} elseif ($view === 'users') {
    $searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $searchClause = '';
    if (!empty($searchTerm)) {
        $searchClause = " WHERE nombre LIKE '%{$searchTerm}%' OR apellido LIKE '%{$searchTerm}%' OR cedula LIKE '%{$searchTerm}%'";
    }
    
    $query_usuarios = "SELECT id_usuario, cedula, nombre, apellido, profesion, telefono, sexo, direccion, fecha_nacimiento, fecha_registro
    FROM usuarios
    $searchClause
    ORDER BY fecha_registro DESC";
    $result_usuarios = $conn->query($query_usuarios);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Enlazar el archivo CSS -->
    <link rel="stylesheet" href="style1.css">
    <!-- Opcional: Enlazar Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- SheetJS para exportación a Excel -->
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <i class="fas fa-cogs"></i> Admin Panel
        </div>
        <nav class="main-nav">
            <a href="dashboard.php?view=attendance" class="<?php echo ($view === 'attendance') ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i> Asistencia</a>
            <a href="dashboard.php?view=users" class="<?php echo ($view === 'users') ? 'active' : ''; ?>"><i class="fas fa-users"></i> Usuarios</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </nav>
         <div class="user-area">
            <span class="welcome-message">Bienvenido,<?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></span>
        </div>
    </header>

    <?php if ($view === 'attendance'): ?>
    <div class="toolbar">
        <div class="filter-group">
            <label for="filterDate"><i class="fas fa-calendar-alt"></i> Filtrar por Fecha:</label>
            <input type="date" id="filter-date" 
                   value="<?= isset($_GET['filterDate']) ? htmlspecialchars($_GET['filterDate']) : date('Y-m-d') ?>"
                   class="...">
        </div>
        <button id="refresh-button" class="btn-refresh"><i class="fas fa-sync-alt"></i> Actualizar</button>
        <!-- Botón para exportar asistencia -->
        <a href="export_attendance.php<?= isset($_GET['filterDate']) ? '?filterDate=' . htmlspecialchars($_GET['filterDate']) : '' ?>" class="btn-export"><i class="fas fa-file-excel"></i> Exportar Asistencia</a>
    </div>

    <div class="container">
        
            <section class="content-section">
                <h2><i class="fas fa-clipboard-list"></i> Registros de Asistencia</h2>
                <p class="section-description">Listado de todas las entradas y salidas registradas.</p>

                <?php if ($result_registros && $result_registros->num_rows > 0): ?>
                <!-- Barra de búsqueda (opcional, puedes implementar la lógica con JS/PHP) -->
                <?php // Search bar removed from attendance view ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Fecha y Hora</th>
                            <th>Tipo</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_registros->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                    <td>
                                        <?php 
                                        echo 'Entrada: ' . htmlspecialchars($row['fecha_hora_entrada']);
                                        if ($row['fecha_hora_salida']) {
                                            echo '<br>Salida: ' . htmlspecialchars($row['fecha_hora_salida']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['tipo_registro'] === 'entrada'): ?>
                                            <span class="status-presente"><i class="fas fa-sign-in-alt entry-icon"></i> Entrada</span>
                                        <?php else: ?>
                                            <span class="status-afuera"><i class="fas fa-sign-out-alt exit-icon"></i> Salida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="observations-cell">
                                        <span class="observations-text"><?php echo !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : 'Ninguna'; ?></span>
                                        <button type="button" class="edit-obs-btn" data-id="<?php echo $row['id_asistencia']; ?>" data-observations="<?php echo htmlspecialchars($row['observaciones'] ?? ''); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button onclick="deleteAttendance(<?php echo $row['id_asistencia']; ?>)" class="delete-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </section>
            <?php else: ?>
                <p>No hay registros de asistencia disponibles.</p>
            <?php endif; ?>
        <?php elseif ($view === 'users'): ?>
             <section class="content-section">
                <h2><i class="fas fa-users"></i> Usuarios Registrados</h2>
                <p class="section-description">Listado de todos los usuarios registrados en el sistema.</p>

                 <!-- Barra de búsqueda (opcional, puedes implementar la lógica con JS/PHP) -->
                <div class="search-bar">
                    <input type="text" id="search-input" placeholder="Buscar por nombre, cédula, etc." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button id="search-button"><i class="fas fa-search"></i> Buscar</button>
                     <!-- Botón para exportar usuarios -->
                    <a href="export_users.php<?= isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : '' ?>" class="btn-export"><i class="fas fa-file-excel"></i> Exportar Usuarios</a>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Profesión</th>
                            <th>Teléfono</th>
                            <th>direccion</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_usuarios && $result_usuarios->num_rows > 0): ?>
                            <?php while ($row = $result_usuarios->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                    <td><?php echo htmlspecialchars($row['profesion']); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($row['direccion'] ?? 'N/A'); ?></td> <!-- Assuming 'email' might not exist in your current query/schema -->
                                    <td><?php echo htmlspecialchars($row['fecha_registro']); ?></td>
                                    <td>
    <button onclick="viewAttendance(<?php echo $row['id_usuario']; ?>)" class="view-btn" title="Ver Asistencia">
        <i class="fas fa-calendar-check"></i>
    </button>
    <button onclick="editUser(<?php echo $row['id_usuario']; ?>)" class="edit-btn" title="Editar Usuario">
        <i class="fas fa-edit"></i>
    </button>
    <button onclick="deleteUser(<?php echo $row['id_usuario']; ?>)" class="delete-btn" title="Eliminar Usuario">
        <i class="fas fa-trash-alt"></i>
    </button>
</td>
                                    
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay usuarios registrados disponibles.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Club Militar App. Todos los derechos reservados.</p>
    </footer>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const refreshButton = document.getElementById('refresh-button');
    const filterDateInput = document.getElementById('filter-date');
    
    function applyDateFilter() {
        const selectedDate = filterDateInput.value;
        if(selectedDate) {
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('view', 'attendance');
            currentParams.set('filterDate', selectedDate);
            window.location.href = 'dashboard.php?' + currentParams.toString();
        }
    }
    
    if(refreshButton && filterDateInput) {
        refreshButton.addEventListener('click', applyDateFilter);
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchButton = document.getElementById('search-button');
    const searchInput = document.getElementById('search-input');
    
    function applySearchFilter() {
        const searchTerm = searchInput.value;
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.set('view', 'users');
        currentParams.set('search', searchTerm);
        window.location.href = 'dashboard.php?' + currentParams.toString();
    }
    
    if(searchButton && searchInput) {
        searchButton.addEventListener('click', applySearchFilter);
    }
});
</script>

<script>
// Función para abrir el modal de edición de observaciones
function openEditObservationsModal(id, observations) {
    const modal = document.getElementById('editObservationsModal');
    document.getElementById('edit_obs_id').value = id;
    document.getElementById('edit_observations').value = observations || '';
    modal.style.display = 'block';
}

// Función para cerrar el modal de edición de observaciones
function closeEditObservationsModal() {
    document.getElementById('editObservationsModal').style.display = 'none';
}

// Cerrar el modal al hacer clic en la X
const closeButtons = document.getElementsByClassName('close');
for (let i = 0; i < closeButtons.length; i++) {
    closeButtons[i].onclick = function() {
        const modals = document.getElementsByClassName('modal');
        for (let j = 0; j < modals.length; j++) {
            modals[j].style.display = 'none';
        }
    };
}

// Cerrar el modal al hacer clic fuera del contenido
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = 'none';
        }
    }
};

// Manejar el envío del formulario de edición de observaciones
document.getElementById('editObservationsForm').onsubmit = function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update_observations.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar la celda de observaciones en la tabla
            const id = formData.get('id_asistencia');
            const observationsText = document.querySelector(`button[data-id="${id}"]`).parentElement.querySelector('.observations-text');
            const newObservations = formData.get('observaciones');
            observationsText.textContent = newObservations || 'Ninguna';
            
            // Actualizar el atributo data-observations del botón
            document.querySelector(`button[data-id="${id}"]`).setAttribute('data-observations', newObservations || '');
            
            // Cerrar el modal
            closeEditObservationsModal();
            
            // Mostrar mensaje de éxito
            alert('¡Observaciones actualizadas correctamente!');
        } else {
            throw new Error(data.message || 'Error al actualizar las observaciones');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar las observaciones: ' + error.message);
    });
};

// Asignar manejadores de eventos a los botones de edición de observaciones
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-obs-btn')) {
            const button = e.target.closest('.edit-obs-btn');
            const id = button.getAttribute('data-id');
            const observations = button.getAttribute('data-observations');
            openEditObservationsModal(id, observations);
        }
    });
});

function deleteAttendance(id) {
    if (confirm('¿Está seguro de que desea eliminar este registro?')) {
        fetch('delete_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Registro eliminado con éxito');
                location.reload();
            } else {
                alert('Error al eliminar el registro: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}
</script>

<script>
function deleteUser(id) {
    if (confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para actualizar la lista
                window.location.reload();
            } else {
                alert('Error al eliminar el usuario: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}
</script>
<!-- Agregar antes del cierre del body -->

<!-- Modal de Edición -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Usuario</h2>
        <form id="editUserForm">
            <input type="hidden" id="edit_id_usuario" name="id_usuario">
            
            <div class="form-group">
                <label for="edit_cedula">Cédula:</label>
                <input type="text" id="edit_cedula" name="cedula" required>
            </div>
            
            <div class="form-group">
                <label for="edit_nombre">Nombre:</label>
                <input type="text" id="edit_nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="edit_apellido">Apellido:</label>
                <input type="text" id="edit_apellido" name="apellido" required>
            </div>
            
            <div class="form-group">
                <label for="edit_profesion">Profesión:</label>
                <input type="text" id="edit_profesion" name="profesion" required>
            </div>
            
            <div class="form-group">
                <label for="edit_telefono">Teléfono:</label>
                <input type="text" id="edit_telefono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="edit_sexo">Sexo:</label>
                <select id="edit_sexo" name="sexo" required>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit_direccion">Dirección:</label>
                <input type="text" id="edit_direccion" name="direccion" required>
            </div>
            
            <div class="form-group">
                <label for="edit_fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="edit_fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            
            <button type="submit" class="btn-primary">Guardar Cambios</button>
        </form>
    </div>
</div>

<style>
/* Estilos para el modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 5px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Estilos para la celda de observaciones */
.observations-cell {
    max-width: 300px;
    word-wrap: break-word;
    position: relative;
    padding-right: 40px !important;
}

.observations-text {
    display: inline-block;
    max-width: calc(100% - 30px);
    vertical-align: middle;
}

.edit-obs-btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    display: none;
}

.edit-obs-btn:hover {
    color: #2196F3;
    background-color: #f0f0f0;
}

tr:hover .edit-obs-btn {
    display: inline-block;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-primary {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-primary:hover {
    background-color: #45a049;
}
</style>

<script>
/* Modificar la función de edición existente */
function editUser(id) {
    // Obtener los datos del usuario
    fetch('edit_user.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            // Llenar el formulario con los datos
            document.getElementById('edit_id_usuario').value = data.id_usuario;
            document.getElementById('edit_cedula').value = data.cedula;
            document.getElementById('edit_nombre').value = data.nombre;
            document.getElementById('edit_apellido').value = data.apellido;
            document.getElementById('edit_profesion').value = data.profesion;
            document.getElementById('edit_telefono').value = data.telefono;
            document.getElementById('edit_sexo').value = data.sexo;
            document.getElementById('edit_direccion').value = data.direccion;
            document.getElementById('edit_fecha_nacimiento').value = data.fecha_nacimiento;
            
            // Mostrar el modal
            document.getElementById('editModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del usuario');
        });
}

// Cerrar el modal
document.querySelector('.close').onclick = function() {
    document.getElementById('editModal').style.display = 'none';
}

// Cerrar el modal si se hace clic fuera de él
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}

// Manejar el envío del formulario
document.getElementById('editUserForm').onsubmit = function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('edit_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Usuario actualizado exitosamente');
            document.getElementById('editModal').style.display = 'none';
            // Recargar la página para mostrar los cambios
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
};
</script>
</body>
</html>

<style>
/* Añadir estilo para el botón de exportar */
.btn-export {
    background-color: #2ecc71; /* Verde */
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background-color 0.3s ease;
    text-decoration: none; /* Para que no parezca un enlace */
    display: inline-block; /* Para que se comporte como un botón */
    margin-left: 10px; /* Espacio entre botones */
}

.btn-export i {
    margin-right: 5px;
}

.btn-export:hover {
    background-color: #27ad60; /* Verde más oscuro */
}

/* Estilos para el botón de ver asistencia */
.view-btn {
    background-color: #17a2b8;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 5px;
    transition: background-color 0.3s;
}

.view-btn:hover {
    background-color: #138496;
}

/* Estilos para el modal de asistencia */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 10px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

/* Estilos para la tabla de asistencia */
.attendance-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.attendance-table th,
.attendance-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.attendance-table th {
    background-color: #f8f9fa;
    font-weight: bold;
}

.attendance-table tr:hover {
    background-color: #f5f5f5;
}

/* Estilos para el encabezado del modal */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5em;
    color: #333;
}

/* Estilos para el botón de actualizar */
.btn-refresh {
    background: none;
    border: none;
    color: #17a2b8;
    cursor: pointer;
    font-size: 1.2em;
    margin-right: 15px;
}

.btn-refresh:hover {
    color: #138496;
}

/* Estilos para el contenedor de filtros */
.filter-container {
    margin: 15px 0;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

/* Estilos para mensajes */
.no-data {
    text-align: center;
    padding: 20px;
    color: #6c757d;
    font-style: italic;
}

.error {
    color: #dc3545;
    text-align: center;
    padding: 10px;
}

/* Estilos para el modal de asistencia */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 25px;
    border: 1px solid #ddd;
    border-radius: 8px;
    width: 85%;
    max-width: 1000px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5em;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.close:hover,
.close:focus {
    color: #333;
    text-decoration: none;
}

/* Estilos para la tabla de asistencia */
.attendance-summary {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #3498db;
}

.attendance-summary h3 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.attendance-summary p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.9em;
}

.table-responsive {
    overflow-x: auto;
    margin-bottom: 20px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.attendance-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95em;
}

.attendance-table th,
.attendance-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.attendance-table th {
    background-color: #f5f7fa;
    color: #2c3e50;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8em;
    letter-spacing: 0.5px;
}

.attendance-table tbody tr:hover {
    background-color: #f8f9fa;
}

.attendance-table td.hours-worked {
    font-weight: 600;
    color: #27ae60;
}

/* Estilos para los estados de carga y mensajes */
.loading,
.no-data,
.error-message {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
}

.loading i,
.no-data i,
.error-message i {
    font-size: 3em;
    margin-bottom: 15px;
    color: #3498db;
}

.no-data i {
    color: #7f8c8d;
}

.error-message i {
    color: #e74c3c;
}

/* Botones */
.btn-refresh {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
    margin-top: 15px;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-refresh:hover {
    background-color: #2980b9;
}

.btn-refresh i {
    font-size: 0.9em;
}

/* Contenedor de exportación */
.export-container {
    margin-top: 20px;
    text-align: right;
}

.btn-export {
    background-color: #27ae60;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-export:hover {
    background-color: #219653;
}

.btn-export i {
    font-size: 0.9em;
}

/* Animación de carga */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading i.fa-spinner {
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
    color: #3498db;
    font-size: 2em;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 20px auto;
        padding: 15px;
    }
    
    .attendance-table {
        font-size: 0.85em;
    }
    
    .attendance-table th,
    .attendance-table td {
        padding: 8px 10px;
    }
    
    .modal-header h2 {
        font-size: 1.3em;
    }
    
    .attendance-summary h3 {
        font-size: 1.1em;
    }
}

@media (max-width: 480px) {
    .modal-content {
        width: 98%;
        margin: 10px auto;
        padding: 10px;
    }
    
    .attendance-table {
        font-size: 0.8em;
    }
    
    .attendance-table th,
    .attendance-table td {
        padding: 6px 8px;
    }
    
    .modal-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .modal-header h2 {
        font-size: 1.2em;
        margin-bottom: 10px;
    }
    
    .close {
        position: absolute;
        top: 10px;
        right: 15px;
    }
}
</style>

<script>
// Función para ver la asistencia de un usuario
function viewAttendance(userId) {
    console.log('1. Iniciando viewAttendance para el usuario ID:', userId);
    
    // Verificar si el usuario tiene permisos
    if (!userId) {
        console.error('Error: No se proporcionó un ID de usuario');
        alert('Error: No se pudo cargar la asistencia. Falta el ID de usuario.');
        return;
    }
    
    // Guardar el ID del usuario en un campo oculto
    const userIdField = document.getElementById('attendanceUserId');
    if (!userIdField) {
        console.error('Error: No se encontró el campo oculto attendanceUserId');
        alert('Error en la configuración de la página. Por favor, recargue.');
        return;
    }
    userIdField.value = userId;
    
    // Mostrar el modal
    const modal = document.getElementById('attendanceModal');
    if (!modal) {
        console.error('Error: No se encontró el modal con ID attendanceModal');
        alert('Error al cargar el visor de asistencia. El elemento no existe.');
        return;
    }
    
    console.log('2. Mostrando el modal');
    modal.style.display = 'block';
    
    // Establecer la fecha actual
    console.log('3. Estableciendo fecha actual');
    showCurrentMonth();
}

// Función para cargar la asistencia de un usuario
function loadUserAttendance(userId, month, year) {
    console.log('8. loadUserAttendance: Iniciando carga de asistencia');
    console.log('   - Usuario ID:', userId);
    console.log('   - Mes:', month);
    console.log('   - Año:', year);
    
    const attendanceList = document.getElementById('attendanceList');
    if (!attendanceList) {
        console.error('Error: No se encontró el contenedor de asistencia');
        alert('Error: No se pudo encontrar el contenedor de asistencia');
        return;
    }
    
    // Mostrar indicador de carga
    attendanceList.innerHTML = `
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando datos de asistencia...</p>
        </div>`;
    
    const url = `get_user_attendance.php?user_id=${userId}&month=${month}&year=${year}`;
    console.log('9. Realizando petición a:', url);
    
    fetch(url)
        .then(response => {
            console.log('10. Respuesta recibida. Estado:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    // Si hay un error en la respuesta JSON
                    throw new Error(err.error || 'Error en la respuesta del servidor');
                }).catch(() => {
                    // Si no se puede leer como JSON, lanzar error genérico
                    throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('11. Datos recibidos:', data);
            
            // Verificar si data es un array
            if (!Array.isArray(data)) {
                throw new Error('Formato de datos inválido');
            }
            
            if (data.length > 0) {
                let html = `
                    <div class="attendance-summary">
                        <h3>${data[0].nombre} ${data[0].apellido} - C.I. ${data[0].cedula}</h3>
                        <p>Mostrando registros de ${month}/${year}</p>
                    </div>
                    <div class="table-responsive">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora Entrada</th>
                                    <th>Hora Salida</th>
                                    <th>Horas Trabajadas</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                data.forEach(record => {
                    if (!record.fecha_hora_entrada) return; // Saltar registros sin fecha de entrada
                    
                    const entryTime = new Date(record.fecha_hora_entrada);
                    const exitTime = record.fecha_hora_salida ? new Date(record.fecha_hora_salida) : null;
                    
                    // Calcular horas trabajadas
                    let hoursWorked = 'N/A';
                    if (exitTime) {
                        const diffMs = exitTime - entryTime;
                        const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                        const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                        hoursWorked = `${String(diffHrs).padStart(2, '0')}h ${String(diffMins).padStart(2, '0')}m`;
                    }
                    
                    // Formatear fechas
                    const options = { 
                        year: 'numeric', 
                        month: '2-digit', 
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    };
                    
                    html += `
                        <tr>
                            <td>${entryTime.toLocaleDateString('es-VE')}</td>
                            <td>${entryTime.toLocaleTimeString('es-VE', options)}</td>
                            <td>${exitTime ? exitTime.toLocaleTimeString('es-VE', options) : 'No registrada'}</td>
                            <td class="hours-worked">${hoursWorked}</td>
                            <td class="observations">${record.observaciones || '-'}</td>
                        </tr>`;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>`;
                
                // Añadir botón de exportar si hay datos
                if (data.length > 0) {
                    html += `
                    <div class="export-container">
                        <button onclick="exportToExcel()" class="btn-export">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </button>
                    </div>`;
                }
                
                attendanceList.innerHTML = html;
            } else {
                attendanceList.innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-calendar-times"></i>
                        <p>No se encontraron registros de asistencia para el período seleccionado.</p>
                    </div>`;
            }
        })
        .catch(error => {
            console.error('12. Error en la carga de asistencia:', error);
            let errorMessage = 'Error al cargar la asistencia. Por favor, intente nuevamente.';
            
            if (error.message.includes('Failed to fetch')) {
                errorMessage = 'No se pudo conectar con el servidor. Verifique su conexión a Internet.';
            } else if (error.message.includes('Unexpected token')) {
                errorMessage = 'Error en el formato de los datos recibidos del servidor.';
            } else if (error.message) {
                // Usar el mensaje de error del servidor si está disponible
                errorMessage = error.message;
            }
            
            // Mostrar mensaje de error en la interfaz
            attendanceList.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${errorMessage}</p>
                    <button onclick="refreshAttendance()" class="btn-refresh">
                        <i class="fas fa-sync-alt"></i> Reintentar
                    </button>
                </div>`;
            
            // Mostrar alerta para errores críticos
            if (error.message.includes('Failed to fetch') || 
                error.message.includes('Unexpected token') || 
                error.message.includes('Error HTTP')) {
                alert(`Error: ${errorMessage}`);
            }
        });
}

// Función para exportar a Excel con formato mejorado
function exportToExcel() {
    const table = document.querySelector('.attendance-table');
    if (!table) {
        console.error('No se encontró la tabla de asistencia');
        return;
    }
    
    // Obtener la cédula y nombre del encabezado de la tabla
    let cedula = '';
    let nombreCompleto = '';
    
    // Buscar el resumen de asistencia que contiene la cédula y nombre
    const summaryElement = document.querySelector('.attendance-summary h3');
    if (summaryElement) {
        const summaryText = summaryElement.textContent.trim();
        // Buscar el patrón: Nombre Apellido - C.I. 12345678
        const match = summaryText.match(/(.+?)\s+-\s*C\.I\.\s*(\d+)/i);
        if (match) {
            nombreCompleto = match[1].trim();
            cedula = match[2].trim();
        }
    }
    
    // Obtener el mes y año del filtro si existe
    let monthYearText = '';
    const monthInput = document.getElementById('filterMonth');
    if (monthInput && monthInput.value) {
        const [year, month] = monthInput.value.split('-');
        const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        monthYearText = `${monthNames[parseInt(month) - 1]} ${year}`;
    }

    // Obtener filas de la tabla
    const rows = table.querySelectorAll('tr');
    if (rows.length <= 1) { // Solo encabezados o tabla vacía
        alert('No hay datos para exportar');
        return;
    }

    // Crear el HTML para Excel
    let html = `
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
            .header { text-align: center; margin-bottom: 15px; }
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
            ${nombreCompleto ? `<div class="subtitle">${nombreCompleto}</div>` : ''}
            ${cedula ? `<div class="subtitle">CÉDULA: ${cedula}</div>` : ''}
            ${monthYearText ? `<div class="subtitle">PERÍODO: ${monthYearText}</div>` : ''}
            <div class="date">Generado el: ${new Date().toLocaleString('es-VE')}</div>
        </div>
        <table border="1">
    `;

    // Agregar encabezados
    const headerRow = rows[0];
    html += '<tr>';
    headerRow.querySelectorAll('th').forEach(th => {
        html += `<th>${th.innerText.trim()}</th>`;
    });
    html += '</tr>';

    // Agregar filas de datos
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td');
        
        html += '<tr>';
        cols.forEach((col, index) => {
            let cellText = col.innerText.trim();
            // Limpiar formato de fechas si es necesario
            if (index === 1) { // Asumiendo que la columna 1 es la de fecha
                const dateMatch = cellText.match(/(\d{2}\/\d{2}\/\d{4})/);
                if (dateMatch) {
                    cellText = dateMatch[0];
                }
            }
            html += `<td>${cellText}</td>`;
        });
        html += '</tr>';
    }

    html += `
        </table>
    </body>
    </html>`;

    // Crear un blob con el contenido HTML
    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    
    // Crear un enlace para descargar el archivo
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    
    // Formatear el nombre del archivo como cedula_mes-año.xls
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    const fileName = `${cedula}_${month}-${year}.xls`;
    
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    
    // Limpiar
    setTimeout(() => {
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }, 0);
    
    // Aplicar estilos a las celdas
    const range = XLSX.utils.decode_range(ws['!ref']);
    
    // Aplicar formato a las celdas
    for (let R = 0; R <= range.e.r; ++R) {
        for (let C = 0; C <= range.e.c; ++C) {
            const cell_address = { c: C, r: R };
            const cell_ref = XLSX.utils.encode_cell(cell_address);
            
            if (!ws[cell_ref]) continue;
            
            // Aplicar estilos según la fila
            if (R === 0) { // Título principal
                ws[cell_ref].s = {
                    font: { bold: true, sz: 16, color: { rgb: "1F4E79" } },
                    alignment: { horizontal: "center", vertical: "center" }
                };
            } else if (R === 1) { // Subtítulo
                ws[cell_ref].s = {
                    font: { bold: true, sz: 14, color: { rgb: "1F4E79" } },
                    alignment: { horizontal: "center", vertical: "center" }
                };
            } else if (R === 6) { // Encabezados de la tabla (fila 6 porque hay 6 filas de encabezado)
                ws[cell_ref].s = {
                    font: { bold: true, color: { rgb: "FFFFFF" } },
                    fill: { fgColor: { rgb: "1F4E79" } },
                    alignment: { horizontal: "center" },
                    border: {
                        top: { style: 'thin', color: { rgb: "1F4E79" } },
                        bottom: { style: 'thin', color: { rgb: "1F4E79" } },
                        left: { style: 'thin', color: { rgb: "1F4E79" } },
                        right: { style: 'thin', color: { rgb: "1F4E79" } }
                    }
                };
            } else if (R > 6) { // Filas de datos
                ws[cell_ref].s = {
                    border: {
                        top: { style: 'thin', color: { rgb: "D9D9D9" } },
                        bottom: { style: 'thin', color: { rgb: "D9D9D9" } },
                        left: { style: 'thin', color: { rgb: "D9D9D9" } },
                        right: { style: 'thin', color: { rgb: "D9D9D9" } }
                    }
                };
            }
        }
    }
    
    // Añadir la hoja al libro
    XLSX.utils.book_append_sheet(wb, ws, 'Asistencia');
    
    try {
        // Generar el archivo Excel
        const excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
        
        // Crear un blob a partir del buffer
        const blob = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        
        // Crear un enlace temporal para la descarga
        const a = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        // Configurar el enlace para la descarga
        a.href = url;
        a.download = fileName;
        
        // Simular clic en el enlace para iniciar la descarga
        document.body.appendChild(a);
        a.click();
        
        // Limpiar
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }, 100);
    } catch (error) {
        console.error('Error al generar el archivo Excel:', error);
        alert('Ocurrió un error al generar el archivo Excel. Por favor, intente nuevamente.');
    }
}

// Función para filtrar por mes y año
function filterAttendanceByMonth() {
    const monthInput = document.getElementById('filterMonth');
    const userId = document.getElementById('attendanceUserId').value;
    
    if (userId && monthInput.value) {
        // El valor de input type="month" ya está en formato YYYY-MM
        const [year, month] = monthInput.value.split('-').map(Number);
        loadUserAttendance(userId, month, year);
    } else if (userId) {
        // Si no hay mes seleccionado, cargar el mes actual
        const today = new Date();
        loadUserAttendance(userId, today.getMonth() + 1, today.getFullYear());
    }
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    
}

// Función para mostrar el mes actual
function showCurrentMonth() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    
    console.log(`Mostrando mes actual: ${year}-${month}`);
    
    // Establecer el mes actual en el input
    const monthInput = document.getElementById('filterMonth');
    if (!monthInput) {
        console.error('Error: No se encontró el input de mes con ID filterMonth');
        return;
    }
    
    monthInput.value = `${year}-${month}`;
    
    // Forzar la actualización del filtro
    const userId = document.getElementById('attendanceUserId').value;
    if (userId) {
        console.log('Cargando asistencia para usuario ID:', userId, 'Mes:', month, 'Año:', year);
        loadUserAttendance(userId, parseInt(month), year);
    }
}

// Función para actualizar la asistencia
function refreshAttendance() {
    const dateInput = document.getElementById('filterDate');
    const userId = document.getElementById('attendanceUserId').value;
    
    if (userId && dateInput.value) {
        const selectedDate = new Date(dateInput.value);
        const month = selectedDate.getMonth() + 1;
        const year = selectedDate.getFullYear();
        
        loadUserAttendance(userId, month, year);
    } else {
        showCurrentMonth();
    }
}

// Cerrar el modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('attendanceModal');
    const editModal = document.getElementById('editModal');
    const obsModal = document.getElementById('editObservationsModal');
    
    if (event.target == modal) {
        modal.style.display = 'none';
    } else if (event.target == editModal) {
        editModal.style.display = 'none';
    } else if (event.target == obsModal) {
        obsModal.style.display = 'none';
    }
}

// Modificar la función de edición existente
function editUser(id) {
    // Obtener los datos del usuario
    fetch('edit_user.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            // Llenar el formulario con los datos
            document.getElementById('edit_id_usuario').value = data.id_usuario;
            document.getElementById('edit_cedula').value = data.cedula;
            document.getElementById('edit_nombre').value = data.nombre;
            document.getElementById('edit_apellido').value = data.apellido;
            document.getElementById('edit_profesion').value = data.profesion;
            document.getElementById('edit_telefono').value = data.telefono;
            document.getElementById('edit_sexo').value = data.sexo;
            document.getElementById('edit_direccion').value = data.direccion;
            document.getElementById('edit_fecha_nacimiento').value = data.fecha_nacimiento;
            
            // Mostrar el modal
            document.getElementById('editModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del usuario');
        });
}

// Cerrar el modal
document.querySelector('.close').onclick = function() {
    document.getElementById('editModal').style.display = 'none';
}

// Cerrar el modal si se hace clic fuera de él
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}

// Manejar el envío del formulario
document.getElementById('editUserForm').onsubmit = function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('edit_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Usuario actualizado exitosamente');
            document.getElementById('editModal').style.display = 'none';
            // Recargar la página para mostrar los cambios
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
};
</script>
<footer>
        <p>&copy; 2025 Club Militar. Todos los derechos reservados.</p>
    </footer>
    
    <!-- Modal para editar observaciones -->
    <style>
    .observations-cell {
        position: relative;
        padding-right: 35px !important;
    }
    .observations-text {
        display: inline-block;
        max-width: calc(100% - 25px);
        vertical-align: middle;
    }
    .edit-obs-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 5px;
        border-radius: 3px;
        display: none;
    }
    .edit-obs-btn:hover {
        color: #2196F3;
        background-color: #f0f0f0;
    }
    tr:hover .edit-obs-btn {
        display: inline-block;
    }
    </style>
    <div id="editObservationsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Observaciones</h2>
            <form id="editObservationsForm">
                <input type="hidden" id="edit_obs_id" name="id_asistencia">
                <div class="form-group">
                    <label for="edit_observations">Observaciones:</label>
                    <textarea id="edit_observations" name="observaciones" rows="4" class="form-control"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditObservationsModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para ver asistencia -->
    <div id="attendanceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Historial de Asistencia</h2>
                <div>
                    <button onclick="refreshAttendance()" class="btn-refresh" title="Actualizar">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <span class="close" onclick="document.getElementById('attendanceModal').style.display='none';">&times;</span>
                </div>
            </div>
            
            <!-- Controles de filtro -->
            <div class="filter-container">
                <div>
                    <label for="filterMonth">Filtrar por mes y año:</label>
                    <input type="month" id="filterMonth" onchange="filterAttendanceByMonth()">
                    <button onclick="showCurrentMonth()" style="margin-left: 10px;">Mes Actual</button>
                </div>
            </div>
            
            <!-- Contenedor para los datos de asistencia -->
            <div id="attendanceList" class="attendance-list">
                <p>Seleccione una fecha para ver los registros de asistencia.</p>
            </div>
            
            <!-- Campo oculto para almacenar el ID del usuario -->
            <input type="hidden" id="attendanceUserId">
        </div>
    </div>
    
    <!-- Modal para editar observaciones -->
    <div id="editObservationsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditObservationsModal()">&times;</span>
            <h3>Editar Observaciones</h3>
            <form id="editObservationsForm">
                <input type="hidden" name="id_asistencia" id="edit_obs_id">
                <div class="form-group">
                    <label for="edit_observations">Observaciones:</label>
                    <textarea name="observaciones" id="edit_observations" rows="4" class="form-control"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditObservationsModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Incluir el archivo JavaScript de observaciones -->
    <script src="js/observations.js"></script>
</body>
</html>
