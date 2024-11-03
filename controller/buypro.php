<?php
// Incluir archivos necesarios
include("../model/buypro.php");
include_once("../model/connectionDB.php");
// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**se verifica que exista la sesion usuario */
if (isset($_SESSION['user_tour'])) {
    /** si existe la sesion usuario se verofoca que tengas un usuaeio */
    if ($_SESSION['user_tour'] == '') {
        echo 0;
    } else {
        // ID del usuario (obtenido de la sesión y sanitizado) para verificar los sitios a los que le dio like
        $id_documento = isset($_SESSION['user_tour']['id_documento']) ? (int)$_SESSION['user_tour']['id_documento'] : 0;
        $_SESSION['user_tour']['rol'] = 'administrador' ;
        echo updateUserToAdmin($conexion, $id_documento);
    }
} else {
    echo 0;
}


// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().