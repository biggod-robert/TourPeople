<?php
// Incluir archivos necesarios
include("../model/restaurantes.php");
include_once("../model/connectionDB.php");

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Obtener el ID del sitio de la solicitud
$id_hotel = isset($_POST['id_restaurante']) ? (int)$_POST['id_restaurante'] : 0;

// Verificar que el ID del sitio es válido
if ($id_hotel > 0) {
    echo json_encode(getRestauranteDetalles($conexion, $id_restaurante));
}

// Cerrar la conexión a la base de datos
$conexion = null; 

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().