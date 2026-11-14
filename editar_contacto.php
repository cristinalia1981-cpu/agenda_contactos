<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Conectar con MySQL
require_once __DIR__ . "/configuraciones/conexion.php";

// Función para mostrar datos de forma segura
function escapar($texto) {
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Obtener el identificador del usuario
$id_usuario = (int) $_SESSION["usuario_id"];

// Crear token de seguridad
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Obtener el identificador del contacto desde la URL
$id_contacto = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id_contacto || $id_contacto < 1) {
    http_response_code(400);
    exit("Identificador de contacto no válido.");
}

$mensaje = "";

// Buscar únicamente contactos del usuario autenticado
try {
    $consulta = $conexion->prepare(
        "SELECT id, nombre, telefono, correo, ciudad
         FROM contactos
         WHERE id = ? AND id_usuario = ?"
    );

    $consulta->execute([
        $id_contacto,
        $id_usuario
    ]);

    $contacto = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$contacto) {
        http_response_code(404);
        exit("Contacto no encontrado.");
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("No se pudo cargar el contacto.");
}

// Procesar los cambios del formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $ciudad = trim($_POST["ciudad"] ?? "");

    // Mantener los valores escritos si ocurre un error
    $contacto["nombre"] = $nombre;
    $contacto["telefono"] = $telefono;
    $contacto["correo"] = $correo;
    $contacto["ciudad"] = $ciudad;

    // Verificar el token CSRF
    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $mensaje = "Solicitud no válida. Actualiza la página.";

    } elseif (
        $nombre === "" ||
        $telefono === "" ||
        $correo === "" ||
        $ciudad === ""
    ) {
        $mensaje = "Todos los campos son obligatorios.";

    } elseif (
        mb_strlen($nombre) > 100 ||
        mb_strlen($telefono) > 20 ||
        mb_strlen($correo) > 150 ||
        mb_strlen($ciudad) > 100
    ) {
        $mensaje = "Uno de los campos supera la longitud permitida.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Ingresa un correo electrónico válido.";

    } else {

        try {
            // Actualizar solo el contacto del usuario actual
            $actualizar = $conexion->prepare(
                "UPDATE contactos
                 SET nombre = ?,
                     telefono = ?,
                     correo = ?,
                     ciudad = ?
                 WHERE id = ? AND id_usuario = ?"
            );

            $actualizar->execute([
                $nombre,
                $telefono,
                $correo,
                $ciudad,
                $id_contacto,
                $id_usuario
            ]);

            // Renovar el token
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            // Regresar a la lista de contactos
            header("Location: panel.php");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "No se pudo actualizar el contacto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar Contacto - Mi Agenda Personal</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .contenedor {
            background-color: white;
            padding: 35px;
            width: 380px;
            max-width: calc(100% - 40px);
            box-sizing: border-box;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #1e3a5f;
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input:focus {
            border-color: #2563eb;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1d4ed8;
        }

        .error {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            background-color: #fee2e2;
            color: #991b1b;
            text-align: center;
        }

        .volver {
            text-align: center;
            margin-top: 20px;
        }

        .volver a {
            color: #2563eb;
            text-decoration: none;
        }

        .volver a:hover {
            text-decoration: underline;
        }
    </style>

</head>

<body>

    <div class="contenedor">

        <h1>Editar Contacto</h1>

        <?php if ($mensaje !== ""): ?>
            <div class="error">
                <?php echo escapar($mensaje); ?>
            </div>
        <?php endif; ?>

        <form
            action="editar_contacto.php?id=<?php
                echo (int) $id_contacto;
            ?>"
            method="POST"
        >

            <!-- Token de seguridad -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?php
                    echo escapar($_SESSION["csrf_token"]);
                ?>"
            >

            <!-- Nombre -->
            <label for="nombre">Nombre completo:</label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="100"
                value="<?php
                    echo escapar($contacto["nombre"]);
                ?>"
                required
            >

            <!-- Teléfono -->
            <label for="telefono">Teléfono:</label>

            <input
                type="tel"
                id="telefono"
                name="telefono"
                maxlength="20"
                value="<?php
                    echo escapar($contacto["telefono"]);
                ?>"
                required
            >

            <!-- Correo -->
            <label for="correo">Correo electrónico:</label>

            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="150"
                value="<?php
                    echo escapar($contacto["correo"]);
                ?>"
                required
            >

            <!-- Ciudad -->
            <label for="ciudad">Ciudad:</label>

            <input
                type="text"
                id="ciudad"
                name="ciudad"
                maxlength="100"
                value="<?php
                    echo escapar($contacto["ciudad"]);
                ?>"
                required
            >

            <!-- Guardar -->
            <button type="submit">
                Guardar Cambios
            </button>

        </form>

        <div class="volver">
            <a href="panel.php">
                Cancelar y volver al panel
            </a>
        </div>

    </div>

</body>
</html>