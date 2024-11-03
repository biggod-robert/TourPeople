<?php
// Incluir archivos necesarios
include("../model/login.php"); // se incluye el modelo del login para reutilizar funciones como buscarUsuario y getUserIdByEmail
include_once("../model/connectionDB.php");
// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Obtener datos del usuario desde el formulario
$newPassword = filter_var($_POST['newPassword'], FILTER_SANITIZE_STRING);
$id_documento = $_SESSION['user_reset_pass'];

// Verificar correo
if (restablecerPassword($conexion, $id_documento, $newPassword) == 1) {
    // si se retablece con exito
    echo json_encode(['codigo' => 1, 'mensaje' => 'Se guardo la nueva contraseña']);
} else {
    //si no se restablece 
    echo json_encode(['codigo' => 0, 'mensaje' => 'error: No fue posible restablecer lacontraseña, refresca la pagina y intentelo de nuevo']);
}

// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().