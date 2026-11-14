<?php
session_start();

// Verificar que el usuario inició sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
require_once __DIR__ . "/configuraciones/conexion.php";

$mensaje = "";
$exito = false;

// Crear token de seguridad
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $ciudad = trim($_POST["ciudad"] ?? "");
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $mensaje = "Solicitud no válida.";

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
        $mensaje = "El correo electrónico no es válido.";

    } else {

        try {
            // El propietario se obtiene de la sesión,
            // nunca del formulario ni de la URL.
            $id_usuario = (int) $_SESSION["usuario_id"];

            $consulta = $conexion->prepare(
                "INSERT INTO contactos
                 (id_usuario, nombre, telefono, correo, ciudad)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $consulta->execute([
                $id_usuario,
                $nombre,
                $telefono,
                $correo,
                $ciudad
            ]);

            $mensaje = "¡Contacto registrado correctamente!";
            $exito = true;

            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "No se pudo registrar el contacto.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Contacto - Mi Agenda</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .contenedor {
            background: white;
            padding: 35px;
            border-radius: 12px;
            width: 380px;
            max-width: calc(100% - 40px);
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #1e3a5f;
            text-align: center;
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
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .mensaje {
            padding: 12px;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
        }

        .exito {
            background: #dcfce7;
            color: #166534;
        }

        .volver {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
<div class="contenedor">

    <h1>Agregar Contacto</h1>

    <?php if ($mensaje !== ""): ?>
        <div class="mensaje <?php echo $exito ? 'exito' : ''; ?>">
            <?php
            echo htmlspecialchars(
                $mensaje,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>
        </div>
    <?php endif; ?>

    <form action="agregar_contacto.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php
            echo htmlspecialchars(
                $_SESSION["csrf_token"],
                ENT_QUOTES,
                "UTF-8"
            );
            ?>"
        >

        <label for="nombre">Nombre completo:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >

        <label for="telefono">Teléfono:</label>
        <input
            type="tel"
            id="telefono"
            name="telefono"
            maxlength="20"
            required
        >

        <label for="correo">Correo electrónico:</label>
        <input
            type="email"
            id="correo"
            name="correo"
            maxlength="150"
            required
        >

        <label for="ciudad">Ciudad:</label>
        <input
            type="text"
            id="ciudad"
            name="ciudad"
            maxlength="100"
            required
        >

        <button type="submit">Guardar Contacto</button>

    </form>

    <div class="volver">
        <a href="panel.php">Volver al panel principal</a>
    </div>

</div>
</body>
</html>