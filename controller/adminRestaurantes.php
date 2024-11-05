<?php
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include("../model/adminRestaurantes.php");
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
$nombreRestaurante = isset($_POST['nombreRestaurante']) ? sanitize_input($_POST['nombreRestaurante']) : "";
$descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : ""; // No sanitizamos para permitir HTML del editor
$direccion = isset($_POST['direccion']) ? sanitize_input($_POST['direccion']) : "";
$enlace_reservas = isset($_POST['enlace_reservas']) ? filter_var($_POST['enlace_reservas'], FILTER_SANITIZE_URL) : "";
$imgPortada = isset($_FILES['imgPortada']) ? $_FILES['imgPortada'] : "";
$imagenes = isset($_FILES['imagen']) ? $_FILES['imagen'] : array();
$id_restaurante = isset($_POST['id_restauranteEdit']) ? (int) $_POST['id_restauranteEdit'] : 0;
$idImagen = isset($_POST['idImagen']) ? (int) $_POST['idImagen'] : 0;
$idRestauranteDelete = isset($_POST['idRestauranteDelete']) ? (int) $_POST['idRestauranteDelete'] : 0;

// ID del usuario (obtenido de la sesión y sanitizado)
$id_documento = isset($_SESSION['user_tour']['id_documento']) ? (int) $_SESSION['user_tour']['id_documento'] : 0;

// Verificar que el ID del usuario esté definido
if (!$id_documento) {
    echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no autenticado']);
    exit;
}

// Validar tamaño y tipo de imagen de portada si se está subiendo
if (!empty($imgPortada)) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1080 * 1080; // 7MB

    if (!in_array($imgPortada['type'], $allowed_types)) {
        echo json_encode(['codigo' => 0, 'mensaje' => 'Tipo de archivo no permitido para la imagen de portada']);
        exit;
    }

    if ($imgPortada['size'] > $max_size) {
        echo json_encode(['codigo' => 0, 'mensaje' => 'La imagen de portada excede el tamaño máximo permitido']);
        exit;
    }
}
// Llamar a la función restaurantes() con datos sanitizados
$data = restaurantes(
    $conexion,
    $opcion,
    $id_documento,
    $nombreRestaurante,
    $descripcion,
    $imgPortada,
    $direccion,
    $enlace_reservas,
    $imagenes,
    $id_restaurante,
    $idImagen,
    $idRestauranteDelete

);

// Devolver datos en formato JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);
// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().

