<?php
// Iniciar la sesión
session_start();

// Conectar con la base de datos
require_once __DIR__ . "/configuraciones/conexion.php";

// Variable para mostrar mensajes
$mensaje = "";

// Crear token de seguridad
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Si el usuario ya inició sesión, enviarlo al panel
if (isset($_SESSION["usuario_id"])) {
    header("Location: panel.php");
    exit;
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    // Validar el token de seguridad
    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        $mensaje = "Solicitud no válida. Actualiza la página.";

    } elseif (
        $correo === "" ||
        $password === "" ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {
        $mensaje = "Ingresa un correo y contraseña válidos.";

    } else {

        try {
            // Buscar el usuario por correo
            $consulta = $conexion->prepare(
                "SELECT id, nombre, correo, password
                 FROM usuarios
                 WHERE correo = ?"
            );

            $consulta->execute([$correo]);

            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            // Verificar la contraseña
            if (
                $usuario &&
                password_verify(
                    $password,
                    $usuario["password"]
                )
            ) {

                // Renovar el identificador de sesión
                session_regenerate_id(true);

                // Guardar los datos del usuario en la sesión
                $_SESSION["usuario_id"] = $usuario["id"];
                $_SESSION["usuario_nombre"] = $usuario["nombre"];
                $_SESSION["usuario_correo"] = $usuario["correo"];

                // Enviar al panel privado
                header("Location: panel.php");
                exit;

            } else {
                $mensaje = "Correo o contraseña incorrectos.";
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = "Ocurrió un error. Intenta nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar Sesión - Mi Agenda Personal</title>

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

        .error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .enlace {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>

</head>

<body>

    <div class="contenedor">

        <h1>Mi Agenda Personal</h1>
        <h2>Iniciar Sesión</h2>

        <?php if ($mensaje !== ""): ?>

            <div class="error">
                <?php
                echo htmlspecialchars(
                    $mensaje,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </div>

        <?php endif; ?>

        <form action="login.php" method="POST">

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

            <label for="correo">Correo electrónico:</label>

            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="150"
                autocomplete="email"
                required
            >

            <label for="password">Contraseña:</label>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">
                Ingresar
            </button>

        </form>

        <div class="enlace">
            ¿No tienes una cuenta?
            <a href="registro.php">Regístrate aquí</a>
        </div>

    </div>

</body>
</html>