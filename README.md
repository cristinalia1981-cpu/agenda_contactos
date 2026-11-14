Agenda Personal de Contactos

Descripción

Agenda Personal de Contactos es una aplicación web desarrollada con PHP y MySQL que permite a los usuarios registrar, consultar, editar y eliminar sus contactos personales.

La aplicación cuenta con un sistema de registro e inicio de sesión que permite que cada usuario administre únicamente sus propios contactos.

Tecnologías utilizadas

PHP

MySQL / MariaDB

HTML

Visual Studio Code

phpMyAdmin

Servidor web local con PHP y MySQL

Funcionalidades

Registro de nuevos usuarios.

Inicio y cierre de sesión.

Agregar contactos personales.

Visualizar los contactos registrados.

Editar información de contactos.

Eliminar contactos con confirmación.

Separación de contactos por usuario.

Protección del acceso a la edición de contactos de otros usuarios.

Base de datos

La aplicación utiliza una base de datos llamada agenda_contactos, que contiene dos tablas principales:

usuarios: almacena la información necesaria para las cuentas de usuario.

contactos: almacena los contactos asociados a cada usuario.

El archivo SQL se encuentra en:

database/agenda_contactos.sql

Instalación

Instalar un entorno local con PHP, MySQL o MariaDB y un servidor web, por ejemplo XAMPP.

Copiar la carpeta agenda_contactos al directorio público del servidor web.

Iniciar Apache y MySQL desde el entorno local.

Abrir phpMyAdmin.

Crear una base de datos llamada agenda_contactos.

Importar el archivo database/agenda_contactos.sql.

Abrir la carpeta configuraciones y realizar una copia del archivo conexion.ejemplo.php.

Cambiar el nombre de la copia a conexion.php.

Abrir conexion.php y configurar el servidor, el nombre de la base de datos, el usuario y la contraseña de MySQL según el entorno local.

Guardar los cambios y acceder a la aplicación desde el navegador mediante http://localhost/agenda_contactos/ si se utiliza XAMPP con su configuración habitual.


Uso

Crear una cuenta desde el formulario de registro.

Iniciar sesión con las credenciales registradas.

Acceder al panel principal.

Agregar, consultar y editar contactos.

Eliminar contactos cuando sea necesario.

Cerrar sesión al finalizar.

Seguridad

La aplicación implementa sesiones de usuario y controles para impedir que una cuenta acceda al formulario de edición de contactos pertenecientes a otra.

Cada usuario puede administrar únicamente sus propios contactos.

Autor

Proyecto académico desarrollado como práctica de programación web con PHP y MySQL.