<?php
session_start();

// Verificar la sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Conexión a MySQL
require_once __DIR__ . "/configuraciones/conexion.php";

// Solo permitir solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

// Verificar token CSRF
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Solicitud no válida.");
}

// Validar el identificador del contacto
$id_contacto = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id_contacto || $id_contacto < 1) {
    http_response_code(400);
    exit("Contacto no válido.");
}

// Identificar al usuario autenticado
$id_usuario = (int) $_SESSION["usuario_id"];

try {
    // Eliminar únicamente contactos del usuario
    $eliminar = $conexion->prepare(
        "DELETE FROM contactos
         WHERE id = ? AND id_usuario = ?"
    );

    $eliminar->execute([
        $id_contacto,
        $id_usuario
    ]);

    // Regresar al panel
    header("Location: panel.php");
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    exit("No se pudo eliminar el contacto.");
}
?>
