<?php
// Incluir archivos necesarios
include("../model/sitios.php");
include_once("../model/connectionDB.php");
// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ID del usuario (obtenido de la sesión y sanitizado) para verificar los sitios a los que le dio like
$id_documento = isset($_SESSION['user_tour']['id_documento']) ? (int)$_SESSION['user_tour']['id_documento'] : 0;
echo json_encode(getSitios($conexion, $id_documento));

// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().