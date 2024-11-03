<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos de conexión
    $servidor = (isset($_POST['host'])) ? filter_var($_POST['host'], FILTER_SANITIZE_STRING) : "";
    $usuario = (isset($_POST['user'])) ? filter_var($_POST['user'], FILTER_SANITIZE_STRING) : "";
    $contrasena = (isset($_POST['pass'])) ? filter_var($_POST['pass'], FILTER_SANITIZE_STRING) : "";
    $nombreBaseDatos = (isset($_POST['nameDB'])) ? filter_var($_POST['nameDB'], FILTER_SANITIZE_STRING) : "";

    $archivoSQL = "../dataBase/db.sql"; // Ruta del archivo SQL
    $rutaConexionDB = "../model/connectionDB.php"; // Ruta del archivo de conexión

    // Crear conexión con MySQL
    $conn = new mysqli($servidor, $usuario, $contrasena);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("La conexión ha fallado: " . $conn->connect_error);
    }

    // Crear la base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS $nombreBaseDatos";
    if ($conn->query($sql) === TRUE) {
        echo "1. Base de datos '$nombreBaseDatos' creada con exito.<br>";
    } else {
        die("Error al crear la base de datos: " . $conn->error);
    }

    // Seleccionar la base de datos
    $conn->select_db($nombreBaseDatos);

    // Cargar y ejecutar el archivo SQL
    $sql = file_get_contents($archivoSQL); // Cargar el archivo SQL

    // Ejecutar las consultas en el archivo
    if ($conn->multi_query($sql)) {
        do {
            // Almacenar resultados si es necesario
            if ($result = $conn->store_result()) {
                $result->free(); // Liberar el conjunto de resultados si es necesario
            }
        } while ($conn->next_result()); // Ir al siguiente resultado
        echo "2. Contenido importado a la base de datos con exito<br>";
    } else {
        echo "Error en la consulta: " . $conn->error . "<br>";
    }

    // Crear archivo de conexión
    $contenidoConexion = "<?php\n";
    $contenidoConexion .= "class Connection\n";
    $contenidoConexion .= "{\n";
    $contenidoConexion .= "    public static function Conectar()\n";
    $contenidoConexion .= "    {\n";
    $contenidoConexion .= "        define('server', '$servidor');\n";
    $contenidoConexion .= "        define('name_db', '$nombreBaseDatos');\n";
    $contenidoConexion .= "        define('user', '$usuario');\n";
    $contenidoConexion .= "        define('password', '$contrasena');\n";
    $contenidoConexion .= "        \$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');\n";
    $contenidoConexion .= "        try {\n";
    $contenidoConexion .= "            \$connection = new PDO(\"mysql:host=\" . server . \"; dbname=\" . name_db, user, password, \$options);\n";
    $contenidoConexion .= "            return \$connection;\n";
    $contenidoConexion .= "        } catch (Exception \$e) {\n";
    $contenidoConexion .= "            die(\"El error de connection es: \" . \$e->getMessage());\n";
    $contenidoConexion .= "        }\n";
    $contenidoConexion .= "    }\n";
    $contenidoConexion .= "}\n";

    // Escribir el archivo de conexión
    file_put_contents($rutaConexionDB, $contenidoConexion);
    echo "3. Archivo de conexión creado exitosamente.<br>";

    // Cerrar la conexión
    $conn->close();
}

?>
