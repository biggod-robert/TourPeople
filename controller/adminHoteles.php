<?php
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include("../model/adminHoteles.php");
include_once("../model/connectionDB.php");

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Función de sanitización
function sanitize_input($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validar y sanitizar entradas
$opcion = isset($_POST['opcion']) ? sanitize_input($_POST['opcion']) : "";
$nombreHotel = isset($_POST['nombreHotel']) ? sanitize_input($_POST['nombreHotel']) : "";
$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : "";
$direccion = isset($_POST['direccion']) ? sanitize_input($_POST['direccion']) : "";
$enlace_reservas = isset($_POST['enlace_reservas']) ? filter_var($_POST['enlace_reservas'], FILTER_SANITIZE_URL) : "";
$imgPortada = isset($_FILES['imgPortada']) ? $_FILES['imgPortada'] : "";
$imgHotel = isset($_FILES['imagen']) ? $_FILES['imagen'] : array();
$idHotel = isset($_POST['idHotel']) ? (int)$_POST['idHotel'] : 0;
$idImagen = isset($_POST['idImagen']) ? (int)$_POST['idImagen'] : 0;
$id_hotelEdit = isset($_POST['id_hotelEdit']) ? (int)$_POST['id_hotelEdit'] : 0;
$idHotelDelete = isset($_POST['idHotelDelete']) ? (int)$_POST['idHotelDelete'] : 0;

// ID del usuario (obtenido de la sesión y sanitizado)
$id_documento = isset($_SESSION['user_tour']['id_documento']) ? (int)$_SESSION['user_tour']['id_documento'] : 0;

// Verificar que el ID del usuario esté definido
if (!$id_documento) {
    echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no autenticado']);
    exit;
}

// Llamar a la función sitios() con datos sanitizados
$data = hoteles(
    $conexion,
    $opcion,
    $id_documento,
    $nombreHotel,
    $descripcion,
    $imgPortada,
    $direccion,
    $enlace_reservas,
    $imgHotel,
    $idHotel,
    $idImagen,
    $id_hotelEdit,
    $idHotelDelete
);

// Devolver datos en formato JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);
// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().