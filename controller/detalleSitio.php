<?php
// Incluir archivos necesarios
include("../model/sitios.php");
include_once("../model/connectionDB.php");

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Obtener el ID del sitio de la solicitud
$id_sitio = isset($_POST['id_sitio']) ? (int)$_POST['id_sitio'] : 0;

// Verificar que el ID del sitio es válido
if ($id_sitio > 0) {
    echo json_encode(getSitioDetalles($conexion, $id_sitio));
}

// Cerrar la conexión a la base de datos
$conexion = null; 

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().