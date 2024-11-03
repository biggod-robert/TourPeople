<?php
// Incluir archivos necesarios
include("../model/hoteles.php");
include_once("../model/connectionDB.php");

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Obtener el ID del sitio de la solicitud
$id_hotel = isset($_POST['id_hotel']) ? (int)$_POST['id_hotel'] : 0;

// Verificar que el ID del sitio es válido
if ($id_hotel > 0) {
    echo json_encode(getHotelDetalles($conexion, $id_hotel));
}

// Cerrar la conexión a la base de datos
$conexion = null; 

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().