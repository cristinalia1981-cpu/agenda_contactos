<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/configuraciones/conexion.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$id_usuario = (int) $_SESSION["usuario_id"];
$nombre = $_SESSION["usuario_nombre"] ?? "";
$correo = $_SESSION["usuario_correo"] ?? "";

function escapar($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Leer los filtros del formulario.
$entrada = $_GET["buscar"] ?? "";
$buscar = is_string($entrada) ? trim($entrada) : "";
$soloFavoritos = ($_GET["favoritos"] ?? "") === "1";
$hayFiltros = $buscar !== "" || $soloFavoritos;

try {
    $sql = "SELECT id, nombre, telefono, correo, ciudad, favorito
            FROM contactos
            WHERE id_usuario = ?";
    $parametros = [$id_usuario];

    if ($soloFavoritos) {
        $sql .= " AND favorito = 1";
    }

    if ($buscar !== "") {
        $sql .= " AND (
            nombre LIKE ? ESCAPE '!'
            OR correo LIKE ? ESCAPE '!'
            OR ciudad LIKE ? ESCAPE '!'
        )";

        $texto = strtr($buscar, ["!" => "!!", "%" => "!%", "_" => "!_"]);
        $patron = "%" . $texto . "%";
        $parametros[] = $patron;
        $parametros[] = $patron;
        $parametros[] = $patron;
    }

    $sql .= " ORDER BY nombre ASC, id ASC";

    $consulta = $conexion->prepare($sql);
    $consulta->execute($parametros);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Mi Agenda Personal</title>

    <style>
        * {
            box-sizing: border-box;
        }

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

        .cabecera-contactos,
        .controles-busqueda,
        .acciones,
        .opcion-favoritos {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cabecera-contactos {
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .boton,
        .boton-editar,
        .boton-eliminar,
        .boton-salir {
            display: inline-block;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font: inherit;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .boton {
            background-color: #2563eb;
        }

        .boton:hover {
            background-color: #1d4ed8;
        }

        .boton-editar {
            background-color: #92400e;
        }

        .boton-editar:hover {
            background-color: #78350f;
        }

        .boton-eliminar,
        .boton-salir {
            background-color: #dc2626;
        }

        .boton-eliminar:hover,
        .boton-salir:hover {
            background-color: #b91c1c;
        }

        .boton-salir {
            margin-top: 10px;
        }

        .buscador {
            margin: 18px 0;
        }

        .etiqueta-busqueda {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .controles-busqueda {
            flex-wrap: wrap;
        }

        .controles-busqueda input[type="search"] {
            width: 340px;
            max-width: 100%;
            padding: 10px 12px;
            border: 1px solid #94a3b8;
            border-radius: 6px;
            font: inherit;
        }

        .opcion-favoritos {
            cursor: pointer;
        }

        .opcion-favoritos input {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .controles-busqueda a {
            color: #1d4ed8;
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

        .contador,
        .vacio {
            color: #555;
        }

        .vacio {
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
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
    </style>
</head>

<body>
    <header>
        <h1>Mi Agenda Personal</h1>
        <p>Panel Principal</p>
        <a href="logout.php" class="boton-salir">Cerrar sesión</a>
    </header>

    <main class="contenedor">
        <section class="tarjeta">
            <h2>¡Bienvenido, <?= escapar($nombre) ?>!</h2>
            <div class="datos">
                <p><strong>Nombre:</strong> <?= escapar($nombre) ?></p>
                <p><strong>Correo electrónico:</strong> <?= escapar($correo) ?></p>
            </div>
        </section>

        <section class="tarjeta">
            <div class="cabecera-contactos">
                <h2>Mis Contactos</h2>
                <a href="agregar_contacto.php" class="boton">+ Agregar Contacto</a>
            </div>

            <form action="panel.php" method="get" class="buscador">
                <label for="buscar" class="etiqueta-busqueda">
                    Buscar por nombre, correo o ciudad
                </label>

                <div class="controles-busqueda">
                    <input
                        type="search"
                        id="buscar"
                        name="buscar"
                        placeholder="Ej. Ana o Cuenca"
                        value="<?= escapar($buscar) ?>"
                    >

                    <label for="favoritos" class="opcion-favoritos">
                        <input
                            type="checkbox"
                            id="favoritos"
                            name="favoritos"
                            value="1"
                            <?= $soloFavoritos ? "checked" : "" ?>
                        >
                        Solo favoritos
                    </label>

                    <button type="submit" class="boton">Buscar</button>

                    <?php if ($hayFiltros): ?>
                        <a href="panel.php">Ver todos los contactos</a>
                    <?php endif; ?>
                </div>
            </form>

            <p class="contador">
                <?= $hayFiltros ? "Coincidencias:" : "Total de contactos:" ?>
                <strong><?= count($contactos) ?></strong>
            </p>

            <?php if (count($contactos) > 0): ?>
                <div class="tabla-contenedor">
                    <table aria-label="Mis contactos">
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Teléfono</th>
                                <th scope="col">Correo electrónico</th>
                                <th scope="col">Ciudad</th>
                                <th scope="col">Favorito</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contactos as $contacto): ?>
                                <tr>
                                    <td><?= escapar($contacto["nombre"]) ?></td>
                                    <td><?= escapar($contacto["telefono"]) ?></td>
                                    <td><?= escapar($contacto["correo"]) ?></td>
                                    <td><?= escapar($contacto["ciudad"]) ?></td>
                                    <td><?= (int) $contacto["favorito"] === 1 ? "Sí" : "No" ?></td>
                                    <td>
                                        <div class="acciones">
                                            <a
                                                class="boton-editar"
                                                href="editar_contacto.php?id=<?= (int) $contacto["id"] ?>"
                                            >Editar</a>

                                            <form
                                                action="eliminar_contacto.php"
                                                method="post"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este contacto?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $contacto["id"] ?>"
                                                >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escapar($_SESSION["csrf_token"]) ?>"
                                                >
                                                <button type="submit" class="boton-eliminar">
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
            <?php elseif ($buscar !== "" && $soloFavoritos): ?>
                <p class="vacio">
                    Ningún favorito coincide con «<?= escapar($buscar) ?>».
                </p>
            <?php elseif ($soloFavoritos): ?>
                <p class="vacio">Todavía no tienes contactos marcados como favoritos.</p>
            <?php elseif ($buscar !== ""): ?>
                <p class="vacio">
                    No se encontraron contactos para «<?= escapar($buscar) ?>».
                </p>
            <?php else: ?>
                <p class="vacio">
                    Todavía no tienes contactos registrados. Puedes agregar tu primer contacto.
                </p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>