<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Conexión con la base de datos
require_once __DIR__ . "/configuraciones/conexion.php";

// Crear token de seguridad CSRF
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Datos del usuario autenticado
$id_usuario = (int) $_SESSION["usuario_id"];
$nombre = $_SESSION["usuario_nombre"] ?? "";
$correo = $_SESSION["usuario_correo"] ?? "";

// Función para mostrar datos de forma segura
function escapar($texto) {
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Consultar únicamente los contactos del usuario
try {

    $consulta = $conexion->prepare(
        "SELECT id, nombre, telefono, correo, ciudad
         FROM contactos
         WHERE id_usuario = ?
         ORDER BY nombre ASC"
    );

    $consulta->execute([$id_usuario]);

    $contactos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    exit("No se pudieron cargar los contactos.");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Panel Principal - Mi Agenda Personal</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
        }

        header {
            background-color: #1e3a5f;
            color: white;
            padding: 25px;
            text-align: center;
        }

        header h1 {
            margin-bottom: 10px;
        }

        .contenedor {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .tarjeta {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        h2 {
            color: #1e3a5f;
        }

        .datos {
            line-height: 1.8;
        }

        .cabecera-contactos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .boton {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 18px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .boton:hover {
            background-color: #1d4ed8;
        }

        .boton-editar {
            display: inline-block;
            background-color: #f59e0b;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .boton-editar:hover {
            background-color: #d97706;
        }

        .boton-eliminar {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .boton-eliminar:hover {
            background-color: #b91c1c;
        }

        .acciones {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .acciones form {
            margin: 0;
        }

        .tabla-contenedor {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #1e3a5f;
            color: white;
        }

        tbody tr:hover {
            background-color: #f1f5f9;
        }

        .contador {
            color: #555;
        }

        .vacio {
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: #555;
        }

        @media (max-width: 600px) {

            .contenedor {
                margin: 15px auto;
                padding: 10px;
            }

            .tarjeta {
                padding: 18px;
            }

            th, td {
                padding: 9px;
                font-size: 13px;
            }
        }
        .boton-salir {
    display: inline-block;
    background-color: #dc2626;
    color: white;
    padding: 10px 18px;
    margin-top: 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
}

.boton-salir:hover {
    background-color: #b91c1c;
}

    </style>

</head>

<body>

    <!-- Encabezado principal -->

   <header>
    <h1>Mi Agenda Personal</h1>
    <p>Panel Principal</p>

    <a href="logout.php" class="boton-salir">
        Cerrar sesión
    </a>
    </header>

    <div class="contenedor">

        <!-- Información del usuario -->
        <div class="tarjeta">

            <h2>
                ¡Bienvenido,
                <?php echo escapar($nombre); ?>!
            </h2>

            <div class="datos">

                <p>
                    <strong>Nombre:</strong>
                    <?php echo escapar($nombre); ?>
                </p>

                <p>
                    <strong>Correo electrónico:</strong>
                    <?php echo escapar($correo); ?>
                </p>

            </div>

        </div>

        <!-- Sección de contactos -->
        <div class="tarjeta">

            <div class="cabecera-contactos">

                <h2>Mis Contactos</h2>

                <a href="agregar_contacto.php" class="boton">
                    + Agregar Contacto
                </a>

            </div>

            <p class="contador">
                Total de contactos:
                <strong>
                    <?php echo count($contactos); ?>
                </strong>
            </p>

            <?php if (count($contactos) > 0): ?>

                <div class="tabla-contenedor">

                    <table>

                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo electrónico</th>
                                <th>Ciudad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($contactos as $contacto): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo escapar(
                                            $contacto["nombre"]
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo escapar(
                                            $contacto["telefono"]
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo escapar(
                                            $contacto["correo"]
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo escapar(
                                            $contacto["ciudad"]
                                        );
                                        ?>
                                    </td>

                                    <!-- Botones Editar y Eliminar -->
                                    <td>

                                        <div class="acciones">

                                            <!-- Botón Editar -->
                                            <a
                                                class="boton-editar"
                                                href="editar_contacto.php?id=<?php
                                                    echo (int) $contacto["id"];
                                                ?>"
                                            >
                                                Editar
                                            </a>

                                            <!-- Botón Eliminar -->
                                            <form
                                                action="eliminar_contacto.php"
                                                method="POST"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este contacto?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php
                                                        echo (int) $contacto["id"];
                                                    ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?php
                                                        echo escapar(
                                                            $_SESSION["csrf_token"]
                                                        );
                                                    ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="boton-eliminar"
                                                >
                                                    Eliminar
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="vacio">
                    Todavía no tienes contactos registrados.
                    Puedes agregar tu primer contacto.
                </div>

            <?php endif; ?>

        </div>

    </div>

</body>
</html>
