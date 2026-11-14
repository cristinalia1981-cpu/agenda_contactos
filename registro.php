<?php
session_start();

// Conexión con la base de datos
require_once __DIR__ . "/configuraciones/conexion.php";

$mensaje = "";
$tipo_mensaje = "";
$nombre = "";
$correo = "";

// Crear token de seguridad para el formulario
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    // Comprobar token de seguridad
    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $mensaje = "Solicitud no válida. Actualiza la página.";
        $tipo_mensaje = "error";

    } elseif (
        $nombre === "" ||
        $correo === "" ||
        $password === ""
    ) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "error";

    } elseif (mb_strlen($nombre) > 100) {
        $mensaje = "El nombre es demasiado largo.";
        $tipo_mensaje = "error";

    } elseif (
        strlen($correo) > 150 ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $mensaje = "Ingresa un correo electrónico válido.";
        $tipo_mensaje = "error";

    } elseif (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        $tipo_mensaje = "error";

    } else {

        try {
            // Verificar si el correo ya existe
            $consulta = $conexion->prepare(
                "SELECT id FROM usuarios WHERE correo = ?"
            );

            $consulta->execute([$correo]);

            if ($consulta->fetch()) {

                $mensaje = "No se pudo completar el registro. Verifica tus datos.";
                $tipo_mensaje = "error";

            } else {

                // Proteger la contraseña
                $password_segura = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Guardar el nuevo usuario
                $insertar = $conexion->prepare(
                    "INSERT INTO usuarios
                    (nombre, correo, password)
                    VALUES (?, ?, ?)"
                );

                $insertar->execute([
                    $nombre,
                    $correo,
                    $password_segura
                ]);

                $mensaje = "¡Usuario registrado correctamente!";
                $tipo_mensaje = "exito";

                $nombre = "";
                $correo = "";

                // Renovar el token de seguridad
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
            }

        } catch (PDOException $e) {

            // Guardar detalles técnicos solo en el registro del servidor
            error_log($e->getMessage());

            $mensaje = "No se pudo completar el registro.";
            $tipo_mensaje = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registro de Usuarios - Mi Agenda</title>

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
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 350px;
            max-width: calc(100% - 40px);
            box-sizing: border-box;
        }

        h1 {
            color: #1e3a5f;
            text-align: center;
            margin-bottom: 5px;
        }

        h2 {
            color: #555;
            text-align: center;
            font-size: 18px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
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

        .mensaje {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }

        .exito {
            background-color: #dcfce7;
            color: #166534;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>

</head>

<body>

    <div class="contenedor">

        <h1>Mi Agenda Personal</h1>
        <h2>Registro de Usuarios</h2>

        <?php if ($mensaje !== ""): ?>

            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php
                echo htmlspecialchars(
                    $mensaje,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </div>

        <?php endif; ?>

        <form action="registro.php" method="POST">

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
                value="<?php
                    echo htmlspecialchars(
                        $nombre,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"
                required
            >

            <label for="correo">Correo electrónico:</label>

            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="150"
                value="<?php
                    echo htmlspecialchars(
                        $correo,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"
                required
            >

            <label for="password">Contraseña:</label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                maxlength="255"
                autocomplete="new-password"
                required
            >

            <button type="submit">
                Registrarse
            </button>

        </form>

    </div>

</body>
</html>