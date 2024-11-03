<?php

/**
 * Controlador general
 */

/** si no existe una sesion se crea */
if (!isset($_SESSION)) session_start();
/* session_destroy(); */
// como primera medida se verifica que la base de datos se encuentre instalada, de no ser asi se redirecciona al instlador del la base de datos.
// Verificar si el archivo de conexión a la base de datos existe
if (!file_exists('../model/connectionDB.php')) {
    // Si el archivo no existe, redirigir a la página de instalación de la base de datos
    header('Location: ../intalador-db/');
    exit(); // Asegurarse de que el script se detenga después de redirigir
} else {
    /**se verifica que exista la sesion usuario */
    if (isset($_SESSION['user_tour'])) {
        /** si existe la sesion usuario se verofoca que tengas un usuaeio */
        if ($_SESSION['user_tour'] == '') {
            header("location:../login/");
            exit(); // termino el script
        } else {
            // segun el rol del usuario cargo la plantilla de vista
            switch ($_SESSION['user_tour']['rol']) {
                case 'administrador':
                    $tipo_user = 'admin';
                    break;
                case 'usuario':
                    // incluyo la configuracion
                    $tipo_user = 'usuario';
                    break;
            }
        }
    } else {
        header("location:../login/");
        exit(); // termino el script
    }
    //recibo la variable de seccion enviada por url
    $seccion = $_GET['seccion'];
    //validacion de las seccciones que requieren permisos de admin
    if ($seccion == 1 || $seccion == 2) {
        // Verifico los permisos de admin
        if ($_SESSION['user_tour']['rol'] !== 'administrador') {
            // Si el usuario no tiene permisos de administrador, va a la sección de usuario
            header("location:../inicio/");
            exit(); // termino el script
        }
    }

    //llamado de la clase ruta
    include 'path.php';
    $new_object = new path();
    $path = $new_object->search_path($seccion);

    // esta variable hace referencia a lo que se va a mostrar en pantalla
    //la variable ruta es el nombre del archivo que se va a mostrar este nombre se trae mediante la clase "Ruta"
    $contenido = "../view/$tipo_user/$path";
    require_once("../view/$tipo_user/template.phtml");
}
