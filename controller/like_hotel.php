<?php
// Incluir archivos necesarios
include("../model/hoteles.php");
include_once("../model/connectionDB.php");
// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// ID del usuario (obtenido de la sesión y sanitizado) para verificar los hoteles a los que le dio like
$id_documento = isset($_SESSION['user_tour']['id_documento']) ? (int)$_SESSION['user_tour']['id_documento'] : 0;
$id_hotel = isset($_POST['id_hotel']) ? filter_var($_POST['id_hotel'], FILTER_SANITIZE_NUMBER_INT) : null;
$like_status = isset($_POST['like_status']) ? filter_var($_POST['like_status'], FILTER_SANITIZE_STRING) : 'none';

// Verifica que los datos sanitizados no sean nulos
if ($id_hotel && $id_documento) {
    // Llama a la función toggleLike
    $resultado = toggleLike($conexion, $id_hotel, $id_documento, $like_status);

    // Retorna la respuesta en formato JSON
    echo json_encode([
        'codigo' => $resultado ? 1 : 0, // Éxito o error
        'mensaje' => $resultado ? 'Operación de like realizada con éxito.' : 'Error al realizar la operación de like.'
    ]);
} else {
    // Retorna un error si falta algún dato
    echo json_encode([
        'codigo' => 0,
        'mensaje' => 'Datos incompletos o inválidos.'
    ]);
}

// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().
