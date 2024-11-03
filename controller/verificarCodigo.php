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
$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
$code = filter_var($_POST['code'], FILTER_SANITIZE_STRING);

// Verificar correo
if (buscarUsuario($conexion, $correo) == 1) {
    // Si se encuentra el correo, se obtiene el ID del usuario
    $id_documento = getUserIdByEmail($conexion, $correo);
    if (validarCodigo($conexion, $id_documento, $code) == 1) {
        //guardo en una variable de seccion el id del usuario a restyablecer la clave para cuando redireccione a la pagina de asignacion de nueva contraseñalo pueda identificar en la secion
        $_SESSION['user_reset_pass'] = $id_documento;
        echo json_encode(['codigo' => 1, 'mensaje' => 'codigo validado con exito.']);
    } else {
        echo json_encode(['codigo' => 0, 'mensaje' => 'el codigo ingresado no es validado.']);
    }
} else {
    echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ingresado no fue encontrado.']);
}

// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().