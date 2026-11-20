# Agenda Personal de Contactos

Aplicación web desarrollada con PHP y MySQL para administrar una agenda personal. Cada usuario puede registrarse, iniciar sesión y gestionar sus propios contactos.

**Aplicación en línea:** [Abrir la Agenda Personal de Contactos](https://agenda-cristina.page.gd/login.php)

**Repositorio:** [Ver el código en GitHub](https://github.com/cristinalia1981-cpu/agenda_contactos)

**Base de datos:** [Descargar el script SQL](database/agenda_contactos.sql)

## Objetivo

Mejorar la agenda desarrollada en la práctica anterior mediante la búsqueda de contactos y la gestión de favoritos, y publicar la aplicación en un hosting gratuito para su evaluación desde Internet.

## Mejoras respecto a la práctica anterior

| Mejora | Cómo utilizarla |
|---|---|
| Búsqueda por nombre, correo o ciudad | Escribe una parte del nombre, del correo electrónico o de la ciudad y pulsa **Buscar**. El panel muestra los contactos que coinciden y la cantidad de resultados. |
| Contactos favoritos | Marca **Marcar como favorito** al agregar o editar un contacto. En el panel, activa **Solo favoritos** y pulsa **Buscar** para ver los contactos marcados. |

Ambos filtros se pueden combinar. Por ejemplo, para buscar favoritos de Quito, escribe `Quito`, marca **Solo favoritos** y pulsa **Buscar**. El enlace **Ver todos los contactos** recupera la lista completa de la cuenta.

## Cómo probar la aplicación en línea

1. Abre la [aplicación publicada](https://agenda-cristina.page.gd/login.php).
2. Selecciona **Regístrate aquí**, crea una cuenta e inicia sesión.
3. Pulsa **+ Agregar Contacto**. Registra dos contactos de prueba con datos ficticios y marca uno como favorito.
4. En el panel, busca primero por nombre, después por correo y finalmente por ciudad.
5. Deja vacío el buscador, marca **Solo favoritos** y pulsa **Buscar**. Debería aparecer únicamente el contacto marcado.
6. Entra en **Editar** para cambiar sus datos o su estado de favorito. La opción **Eliminar** solicita confirmación antes de borrar el contacto.
7. Pulsa **Cerrar sesión** al terminar.

## Capturas de pantalla

### Inicio de sesión

![Formulario de inicio de sesión de la agenda publicada](capturas/inicio-sesion.png)

### Panel de contactos

![Panel con el filtro Solo favoritos activado y cero coincidencias](capturas/busqueda-favoritos.png)

## Tecnologías utilizadas

| Tecnología | Uso en el proyecto |
|---|---|
| PHP y PDO | Procesamiento de formularios, sesiones y consultas preparadas. |
| MySQL / MariaDB | Almacenamiento de usuarios y contactos. |
| HTML y CSS | Formularios, tabla de contactos y diseño adaptable. |
| Apache y XAMPP | Ejecución de la aplicación en el entorno local. |
| phpMyAdmin | Administración de las tablas e importación del script SQL. |
| InfinityFree | Alojamiento de la aplicación PHP y la base de datos en línea. |
| Visual Studio Code, Git y GitHub | Edición del código, control de versiones y publicación del repositorio. |

## Base de datos

El archivo [`database/agenda_contactos.sql`](database/agenda_contactos.sql) contiene la estructura de las tablas `usuarios` y `contactos`. La exportación incluye únicamente la estructura, por lo que cada instalación comienza con una agenda vacía.

La tabla `usuarios` contiene el identificador, el nombre, el correo, el hash de la contraseña y la fecha de registro. La tabla `contactos` contiene los siguientes campos:

| Campo | Descripción |
|---|---|
| `id` | Identificador del contacto. |
| `id_usuario` | Identificador del usuario propietario, relacionado con `usuarios.id`. |
| `nombre` | Nombre del contacto. |
| `telefono` | Número de teléfono. |
| `correo` | Correo electrónico. |
| `ciudad` | Ciudad de residencia. |
| `favorito` | Campo `TINYINT(1)` que guarda `1` para un favorito y `0` para los demás contactos. Su valor predeterminado es `0`. |

## Instalación local con XAMPP

1. Instala XAMPP con PHP 8 y habilita las extensiones `pdo_mysql` y `mbstring`.
2. Descarga o clona el repositorio. Coloca el proyecto en `C:\xampp\htdocs\agenda_contactos` y comprueba que `index.php` esté directamente dentro de esa carpeta.
3. Abre el panel de XAMPP e inicia **Apache** y **MySQL**.
4. Entra en [phpMyAdmin local](http://localhost/phpmyadmin/) y crea una base de datos llamada `agenda_contactos`.
5. Selecciona esa base, abre **Importar** y carga el archivo `database/agenda_contactos.sql`.
6. Copia `configuraciones/conexion.ejemplo.php` y guarda la copia como `configuraciones/conexion.php`.
7. En la copia, configura `localhost` como servidor, `agenda_contactos` como base de datos y las credenciales de MySQL de tu instalación.
8. Abre la [agenda local](http://localhost/agenda_contactos/), registra una cuenta e inicia sesión.

## Seguridad

- Las contraseñas se almacenan mediante `password_hash()` y se verifican con `password_verify()`.
- Las páginas protegidas comprueban la sesión del usuario.
- Las consultas de contactos utilizan PDO y comprueban el identificador del propietario.
- Los formularios de creación, edición y eliminación de contactos utilizan tokens CSRF.
- Los datos mostrados en el panel se escapan antes de insertarlos en el HTML.
- El repositorio proporciona `conexion.ejemplo.php` como plantilla. Las credenciales reales se configuran por separado en cada instalación.

## Autoría

**Cristina Castillo**

Proyecto académico de desarrollo web con PHP y MySQL.
