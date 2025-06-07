# **App Name**: Attendance Tracker

## Core Features:

- Admin Login: Login page for admin to access attendance records.
- User Registration: Public registration page where users can input their ID.
- Check-in: Button to record the check-in date and time for users.
- Check-out: Button to record the check-out date and time for users.
- Attendance Records View: Admin dashboard to visualize attendance logs with user details.

## Style Guidelines:

- Primary color: Deep blue (#3F51B5) to convey professionalism and stability, inspired by the club's military affiliation.
- Background color: Light blue (#E8EAF6), a desaturated tone of the primary, to ensure readability and create a calm atmosphere.
- Accent color: Purple (#7E57C2) for interactive elements, complementing the primary color and adding a touch of sophistication.
- Clear and readable sans-serif fonts for the content.
- Simple and professional icons for navigation and actions.
- Clean and structured layout to ensure easy navigation and focus.

## Mapa de Módulos y Base de Datos

Este sistema se compone de varios módulos principales que interactúan con la base de datos `club_militar_db`.

### Base de Datos (`club_militar_db`)

La base de datos contiene las siguientes tablas principales:

- `usuarios`: Almacena la información de los usuarios registrados (cédula, nombre, apellido, profesion, etc.).
- `asistencia`: Registra los eventos de entrada y salida de los usuarios (id_usuario, fecha_hora_entrada, fecha_hora_salida).
- `administradores`: Almacena las credenciales de los administradores para el acceso al panel de control.

### Módulos del Sistema y su Interacción con la Base de Datos

1.  **Módulo de Registro de Usuarios**
    -   Archivos: <mcfile name="register_user.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\register_user.php"></mcfile>, <mcfile name="process_registration.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\process_registration.php"></mcfile>
    -   Función: Permite a los nuevos usuarios ingresar sus datos.
    -   Interacción DB: Inserta nuevos registros en la tabla `usuarios` a través de <mcfile name="process_registration.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\process_registration.php"></mcfile>.

2.  **Módulo de Control de Asistencia**
    -   Archivos: <mcfile name="index.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\index.php"></mcfile>, <mcfile name="registrar_asistencia.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\includes\registrar_asistencia.php"></mcfile>
    -   Función: Permite a los usuarios registrar su hora de entrada y salida.
    -   Interacción DB: Consulta la tabla `usuarios` para verificar la existencia del usuario e inserta/actualiza registros en la tabla `asistencia` a través de <mcfile name="registrar_asistencia.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\includes\registrar_asistencia.php"></mcfile>.

3.  **Módulo de Administración**
    -   Archivos: Archivos dentro de la carpeta <mcfolder name="admin" path="c:\xampp\htdocs\Alan\club-militar-app-master\admin"></mcfolder> (principalmente <mcfile name="dashboard.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\admin\dashboard.php"></mcfile>, <mcfile name="edit_user.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\admin\edit_user.php"></mcfile>, <mcfile name="delete_user.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\admin\delete_user.php"></mcfile>, <mcfile name="delete_attendance.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\admin\delete_attendance.php"></mcfile>).
    -   Función: Permite a los administradores gestionar el sistema.
    -   Interacción DB: Consulta las tablas `asistencia` y `usuarios` para mostrar registros. Realiza operaciones de eliminación en `asistencia` y `usuarios`, y operaciones de edición en `usuarios`.

4.  **Conexión a la Base de Datos**
    -   Archivo: <mcfile name="db_connect.php" path="c:\xampp\htdocs\Alan\club-militar-app-master\includes\db_connect.php"></mcfile>
    -   Función: Establece la conexión con la base de datos MySQL.
    -   Interacción DB: Es incluido por otros scripts PHP para realizar todas las operaciones de base de datos.

Este mapa proporciona una vista general de cómo los diferentes componentes del sistema interactúan con la base de datos para gestionar usuarios y asistencia.