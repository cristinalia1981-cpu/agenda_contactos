# Agenda Personal de Contactos

## Descripción del proyecto

Agenda Personal de Contactos es una aplicación web desarrollada con **PHP y MySQL**, cuyo objetivo es permitir a los usuarios administrar sus contactos personales de forma organizada y segura.

El sistema implementa autenticación, manejo de sesiones y operaciones de creación, consulta, actualización y eliminación de contactos (CRUD).

Cada usuario puede visualizar y administrar únicamente sus propios contactos, evitando el acceso no autorizado a la información de otras cuentas.

Este proyecto fue desarrollado como parte de una actividad académica de programación web.

## Objetivo

Desarrollar una aplicación web utilizando PHP y MySQL que implemente mecanismos de autenticación, sesiones y gestión de información personal, aplicando buenas prácticas de seguridad y separación de datos por usuario.

## Tecnologías utilizadas

- **PHP:** desarrollo de la lógica del servidor.
- **MySQL / MariaDB:** almacenamiento de usuarios y contactos.
- **HTML:** estructura de las páginas web.
- **CSS:** diseño y presentación de la aplicación.
- **PDO:** conexión segura y consultas preparadas a la base de datos.
- **XAMPP:** entorno de ejecución local con Apache y MySQL/MariaDB.
- **phpMyAdmin:** administración de la base de datos.
- **Visual Studio Code:** edición del código fuente.
- **Git y GitHub:** control de versiones y publicación del proyecto.

## Funcionalidades principales

### Registro de usuarios

- Registro de nuevos usuarios.
- Validación de los datos ingresados.
- Comprobación de correos electrónicos previamente registrados.
- Almacenamiento seguro de contraseñas mediante `password_hash()`.

### Inicio y cierre de sesión

- Autenticación mediante correo electrónico y contraseña.
- Verificación de contraseñas con `password_verify()`.
- Creación y administración de sesiones.
- Mensajes de error cuando las credenciales son incorrectas.
- Cierre de sesión del usuario.

### Panel principal

- Visualización del nombre y correo electrónico del usuario autenticado.
- Listado de contactos personales.
- Contador de contactos registrados.
- Acceso a las funciones de agregar, editar y eliminar contactos.

### Gestión de contactos

La aplicación permite realizar las siguientes operaciones:

1. **Crear:** registrar contactos con nombre, teléfono, correo electrónico y ciudad.
2. **Consultar:** visualizar los contactos asociados al usuario.
3. **Actualizar:** modificar la información de contactos existentes.
4. **Eliminar:** borrar contactos después de solicitar confirmación.

## Base de datos

La aplicación utiliza una base de datos llamada `agenda_contactos`.

Contiene dos tablas principales:

### Tabla usuarios

| Campo | Descripción |
|---|---|
| id | Identificador del usuario |
| nombre | Nombre del usuario |
| correo | Correo electrónico |
| password | Contraseña almacenada mediante hash |
| fecha_registro | Fecha de registro |

### Tabla contactos

| Campo | Descripción |
|---|---|
| id | Identificador del contacto |
| id_usuario | Identificador del usuario propietario |
| nombre | Nombre del contacto |
| telefono | Número telefónico |
| correo | Correo electrónico |
| ciudad | Ciudad de residencia |

La tabla `contactos` está relacionada con la tabla `usuarios` mediante el campo `id_usuario`, utilizando una clave foránea.

El script SQL se encuentra en:

`database/agenda_contactos.sql`

El archivo contiene la estructura de la base de datos sin registros personales de prueba.

## Requisitos para ejecutar la aplicación

Para instalar el proyecto se necesita:

- PHP 8 o una versión compatible.
- MySQL o MariaDB.
- Servidor web Apache.
- XAMPP o un entorno equivalente.
- Navegador web actualizado.
- Extensión PDO MySQL habilitada en PHP.

## Instalación paso a paso

1. Instalar XAMPP en la computadora.
2. Copiar la carpeta `agenda_contactos` dentro de `C:\xampp\htdocs`.
3. Abrir XAMPP Control Panel.
4. Iniciar los servicios **Apache** y **MySQL**.
5. Abrir phpMyAdmin desde `http://localhost/phpmyadmin`.
6. Crear una base de datos llamada `agenda_contactos`.
7. Importar el archivo `database/agenda_contactos.sql`.
8. Entrar a la carpeta `configuraciones`.
9. Crear una copia de `conexion.ejemplo.php` y nombrarla `conexion.php`.
10. Abrir `conexion.php` y configurar el servidor, usuario, contraseña y nombre de la base de datos según la instalación local.
11. Guardar los cambios.
12. Abrir la aplicación desde el navegador en:

`http://localhost/agenda_contactos/`

La página principal redirige al formulario de inicio de sesión.

## Cómo utilizar la aplicación

1. Abrir la aplicación en el navegador.
2. Seleccionar la opción de registro para crear una cuenta.
3. Iniciar sesión con el correo electrónico y la contraseña.
4. Acceder al panel principal.
5. Agregar contactos utilizando el formulario correspondiente.
6. Consultar y editar los contactos registrados.
7. Eliminar contactos cuando sea necesario, confirmando la operación.
8. Cerrar sesión al finalizar.

## Seguridad implementada

El sistema incorpora las siguientes medidas:

- Uso de `password_hash()` para proteger las contraseñas.
- Uso de `password_verify()` para comprobar las credenciales.
- Validación de formularios en el servidor.
- Control de sesiones en páginas protegidas.
- Consultas preparadas mediante PDO para reducir el riesgo de inyección SQL.
- Comprobación de la propiedad de los contactos mediante `id_usuario`.
- Protección frente a la modificación manual de identificadores en las URL.
- Tokens CSRF para proteger las operaciones implementadas mediante formularios.
- Mensajes de error que evitan revelar información sensible de la conexión.

El archivo `configuraciones/conexion.php` se excluye del repositorio mediante `.gitignore`, mientras que `conexion.ejemplo.php` proporciona una plantilla para configurar la aplicación.

## Estructura del proyecto

```text
agenda_contactos/
│
├── configuraciones/
│   └── conexion.ejemplo.php
│
├── database/
│   └── agenda_contactos.sql
│
├── .gitignore
├── .gitattributes
├── README.md
├── index.php
├── registro.php
├── login.php
├── panel.php
├── agregar_contacto.php
├── editar_contacto.php
├── eliminar_contacto.php
└── logout.php
```

El archivo local `conexion.php` debe crearse durante la instalación y no debe publicarse en el repositorio.

## Pruebas realizadas

Durante el desarrollo se comprobó:

- Registro e inicio de sesión de usuarios.
- Visualización de contactos asociados a la cuenta autenticada.
- Protección del formulario de edición frente al acceso a contactos ajenos.
- Creación de contactos.
- Edición de contactos.
- Solicitud de confirmación antes de eliminar un contacto.
- Cancelación de la eliminación sin perder información.
- Eliminación confirmada y actualización de la lista de contactos.
- Redirección desde `index.php` hacia el inicio de sesión.

## Repositorio

El código fuente del proyecto está disponible en GitHub:

https://github.com/cristinalia1981-cpu/agenda_contactos

## Autoría

**Cristina Castillo**

Proyecto académico de desarrollo web con PHP y MySQL.