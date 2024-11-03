<?php
// como primera medida se verifica que la base de datos se encuentre instalada, de no ser asi se redirecciona al instlador del la base de datos.
// Verificar si el archivo de conexión a la base de datos existe
if (!file_exists('model/connectionDB.php')) {
    // Si el archivo no existe, redirigir a la página de instalación de la base de datos
    header('Location: intalador-db/');
    exit(); // Asegurarse de que el script se detenga después de redirigir
} else {
    // Si el archivo existe, redirigir a la página principal
    header('Location: inicio/');
    exit(); // Asegurarse de que el script se detenga después de redirigir
}

