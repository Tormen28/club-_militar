# Sistema de Gestión Club Militar

Este proyecto es un sistema de gestión desarrollado en PHP para un club militar. Permite el registro de usuarios, el control de asistencia y la administración a través de un panel de control.

## Funcionalidades Principales

- **Registro de Usuarios:** Permite a los nuevos miembros registrarse en el sistema con sus datos personales.
- **Control de Asistencia:** Registra la hora de entrada y salida de los miembros.
- **Panel de Administración:** Área restringida para administradores que permite:
  - Visualizar el registro de asistencia.
  - Gestionar los usuarios registrados (ver, editar y eliminar).

## Estructura del Proyecto

- `admin/`: Contiene los archivos del panel de administración (`dashboard.php`, `index.php`, `edit_user.php`, `delete_user.php`, `delete_attendance.php`, `logout.php`, `style1.css`).
- `css/`: Contiene los archivos CSS (`style.css`).
- `includes/`: Contiene archivos de inclusión como la conexión a la base de datos (`db_connect.php`) y la lógica para registrar asistencia (`registrar_asistencia.php`).
- `club_militar_db.sql`: Script SQL para la creación de la base de datos y tablas.
- `index.php`: Página principal o de inicio.
- `process_registration.php`: Script para procesar los datos del formulario de registro de usuarios.
- `register_user.php`: Página del formulario de registro de usuarios.
- `test_db.php`: Archivo para probar la conexión a la base de datos.

## Configuración

1. Configura un servidor web local (como XAMPP) con soporte para PHP y MySQL.
2. Importa el archivo `club_militar_db.sql` a tu gestor de base de datos (por ejemplo, phpMyAdmin) para crear la base de datos y las tablas necesarias.
3. Configura los detalles de conexión a la base de datos en `includes/db_connect.php`.
4. Coloca los archivos del proyecto en el directorio `htdocs` (o el directorio raíz de tu servidor web).
5. Accede al sistema a través de tu navegador web.

---

*Este README ha sido actualizado para reflejar el estado actual del proyecto PHP.*
