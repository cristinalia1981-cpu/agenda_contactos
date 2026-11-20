<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/configuraciones/conexion.php";

function escapar($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function leerCampo($campo) {
    $valor = $_POST[$campo] ?? "";
    return is_string($valor) ? trim($valor) : "";
}

$id_usuario = (int) $_SESSION["usuario_id"];

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$id_contacto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_contacto || $id_contacto < 1) {
    http_response_code(400);
    exit("Identificador de contacto no válido.");
}

$mensaje = "";

// Cargar el contacto y su estado de favorito.
try {
    $consulta = $conexion->prepare(
        "SELECT id, nombre, telefono, correo, ciudad, favorito
         FROM contactos
         WHERE id = ? AND id_usuario = ?"
    );

    $consulta->execute([$id_contacto, $id_usuario]);
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!is_string($token) || !hash_equals($_SESSION["csrf_token"], $token)) {
        $mensaje = "Solicitud no válida. Actualiza la página.";

    } else {
        // Conservar lo escrito si el usuario necesita corregir algún campo.
        $contacto["nombre"] = leerCampo("nombre");
        $contacto["telefono"] = leerCampo("telefono");
        $contacto["correo"] = leerCampo("correo");
        $contacto["ciudad"] = leerCampo("ciudad");
        $contacto["favorito"] = ($_POST["favorito"] ?? "") === "1" ? 1 : 0;

        if (
            $contacto["nombre"] === "" ||
            $contacto["telefono"] === "" ||
            $contacto["correo"] === "" ||
            $contacto["ciudad"] === ""
        ) {
            $mensaje = "Completa el nombre, el teléfono, el correo y la ciudad.";

        } elseif (
            mb_strlen($contacto["nombre"]) > 100 ||
            mb_strlen($contacto["telefono"]) > 20 ||
            mb_strlen($contacto["correo"]) > 150 ||
            mb_strlen($contacto["ciudad"]) > 100
        ) {
            $mensaje = "Uno de los campos supera la longitud permitida.";

        } elseif (!filter_var($contacto["correo"], FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Ingresa un correo electrónico válido.";

        } else {
            try {
                $actualizar = $conexion->prepare(
                    "UPDATE contactos
                     SET nombre = ?, telefono = ?, correo = ?, ciudad = ?, favorito = ?
                     WHERE id = ? AND id_usuario = ?"
                );

                $actualizar->execute([
                    $contacto["nombre"],
                    $contacto["telefono"],
                    $contacto["correo"],
                    $contacto["ciudad"],
                    $contacto["favorito"],
                    $id_contacto,
                    $id_usuario
                ]);

                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
                header("Location: panel.php");
                exit;

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $mensaje = "No se pudo actualizar el contacto.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e3a5f;
            text-align: center;
            margin-bottom: 25px;
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
            font-size: 14px;
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
    <main class="contenedor">
        <h1>Editar Contacto</h1>

        <?php if ($mensaje !== ""): ?>
            <p class="error" role="alert"><?= escapar($mensaje) ?></p>
        <?php endif; ?>

        <form action="editar_contacto.php?id=<?= (int) $id_contacto ?>" method="post">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION["csrf_token"]) ?>"
            >

            <label for="nombre">Nombre completo:</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="100"
                value="<?= escapar($contacto["nombre"]) ?>"
                required
            >

            <label for="telefono">Teléfono:</label>
            <input
                type="tel"
                id="telefono"
                name="telefono"
                maxlength="20"
                value="<?= escapar($contacto["telefono"]) ?>"
                required
            >

            <label for="correo">Correo electrónico:</label>
            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="150"
                value="<?= escapar($contacto["correo"]) ?>"
                required
            >

            <label for="ciudad">Ciudad:</label>
            <input
                type="text"
                id="ciudad"
                name="ciudad"
                maxlength="100"
                value="<?= escapar($contacto["ciudad"]) ?>"
                required
            >

            <label for="favorito" class="opcion-favorito">
                <input
                    type="checkbox"
                    id="favorito"
                    name="favorito"
                    value="1"
                    <?= (int) $contacto["favorito"] === 1 ? "checked" : "" ?>
                >
                Marcar como favorito
            </label>

            <button type="submit">Guardar Cambios</button>
        </form>

        <div class="volver">
            <a href="panel.php">Cancelar y volver al panel</a>
        </div>
    </main>
</body>
</html>