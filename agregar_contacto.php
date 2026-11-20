<?php
session_start();

// Verificar que el usuario inició sesión.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/configuraciones/conexion.php";

$mensaje = "";
$exito = false;
$nombre = "";
$telefono = "";
$correo = "";
$ciudad = "";
$favorito = 0;

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function leerCampo($campo) {
    $valor = $_POST[$campo] ?? "";
    return is_string($valor) ? trim($valor) : "";
}

function escapar($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = leerCampo("nombre");
    $telefono = leerCampo("telefono");
    $correo = leerCampo("correo");
    $ciudad = leerCampo("ciudad");
    $favorito = ($_POST["favorito"] ?? "") === "1" ? 1 : 0;
    $token = $_POST["csrf_token"] ?? "";

    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"], $token)) {
        $mensaje = "Solicitud no válida.";

    } elseif ($nombre === "" || $telefono === "" || $correo === "" || $ciudad === "") {
        $mensaje = "Completa el nombre, el teléfono, el correo y la ciudad.";

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
            $consulta = $conexion->prepare(
                "INSERT INTO contactos
                    (id_usuario, nombre, telefono, correo, ciudad, favorito)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $consulta->execute([
                (int) $_SESSION["usuario_id"],
                $nombre,
                $telefono,
                $correo,
                $ciudad,
                $favorito
            ]);

            $mensaje = "¡Contacto registrado correctamente!";
            $exito = true;
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

            // Dejar el formulario listo para registrar otro contacto.
            $nombre = "";
            $telefono = "";
            $correo = "";
            $ciudad = "";
            $favorito = 0;

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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e3a5f;
            text-align: center;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 15px 0 6px;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"] {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .opcion-favorito {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .opcion-favorito input {
            width: 18px;
            height: 18px;
            margin: 0;
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
    <main class="contenedor">
        <h1>Agregar Contacto</h1>

        <?php if ($mensaje !== ""): ?>
            <p class="mensaje <?= $exito ? 'exito' : '' ?>">
                <?= escapar($mensaje) ?>
            </p>
        <?php endif; ?>

        <form action="agregar_contacto.php" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION['csrf_token']) ?>"
            >

            <label for="nombre">Nombre completo:</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="100"
                value="<?= escapar($nombre) ?>"
                required
            >

            <label for="telefono">Teléfono:</label>
            <input
                type="tel"
                id="telefono"
                name="telefono"
                maxlength="20"
                value="<?= escapar($telefono) ?>"
                required
            >

            <label for="correo">Correo electrónico:</label>
            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="150"
                value="<?= escapar($correo) ?>"
                required
            >

            <label for="ciudad">Ciudad:</label>
            <input
                type="text"
                id="ciudad"
                name="ciudad"
                maxlength="100"
                value="<?= escapar($ciudad) ?>"
                required
            >

            <label for="favorito" class="opcion-favorito">
                <input
                    type="checkbox"
                    id="favorito"
                    name="favorito"
                    value="1"
                    <?= $favorito === 1 ? 'checked' : '' ?>
                >
                Marcar como favorito
            </label>

            <button type="submit">Guardar Contacto</button>
        </form>

        <div class="volver">
            <a href="panel.php">Volver al panel principal</a>
        </div>
    </main>
</body>
</html>