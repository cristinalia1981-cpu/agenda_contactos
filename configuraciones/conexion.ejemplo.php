<?php

$servidor = "localhost";
$base_datos = "agenda_contactos";
$usuario = "TU_USUARIO";
$contrasena = "TU_CONTRASENA";

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("No se pudo conectar a la base de datos.");
}