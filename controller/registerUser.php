<?php
// Incluir archivos necesarios
include("../model/connectionDB.php");
include("../model/registroUser.php"); // Suponiendo que tienes un modelo para manejar usuarios

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener datos del usuario desde el formulario
$documento = filter_var($_POST['documento'], FILTER_SANITIZE_STRING);
$nombre_p = filter_var($_POST['nombre_p'], FILTER_SANITIZE_STRING);
$apellido_p = filter_var($_POST['apellido_p'], FILTER_SANITIZE_STRING);
$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
$clave = filter_var($_POST['clave'], FILTER_SANITIZE_STRING);
$edad = filter_var($_POST['edad'], FILTER_SANITIZE_NUMBER_INT);
$f_nacimiento = filter_var($_POST['f_nacimiento'], FILTER_SANITIZE_STRING);
$telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);
$imagen = ''; // Inicializamos la variable para la imagen

// Manejo de la carga de imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
    $nombreImagen = $_FILES['imagen']['name'];
    $rutaTemporal = $_FILES['imagen']['tmp_name'];
    $ext = pathinfo($nombreImagen, PATHINFO_EXTENSION);
    $nombreImagen = uniqid() . '.' . $ext; // Generar un nombre único para la imagen

    // Validar extensiones de imagen permitidas
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($ext), $extensionesPermitidas)) {
        // Mover la imagen a la carpeta ../upload/imgUsers/
        $rutaDestino = "../upload/imgUsers/" . $nombreImagen;
        if (move_uploaded_file($rutaTemporal, $rutaDestino)) {
            $imagen = $nombreImagen; // Asignar el nombre de la imagen si se mueve correctamente
        } else {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al cargar la imagen.']);
            exit();
        }
    } else {
        echo json_encode(['codigo' => 0, 'mensaje' => 'Formato de imagen no permitido.']);
        exit();
    }
}

// Verificar si el documento ya existe
if (buscarUsuarioPorDocumento($conexion, $documento) == 1) {
    echo json_encode(['codigo' => 0, 'mensaje' => 'El documento ID ya está registrado.']);
    exit();
}

// Verificar si el correo ya existe
if (buscarUsuarioPorCorreo($conexion, $correo) == 1) {
    echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está registrado.']);
    exit();
}

// Registrar nuevo usuario
if (registrarUsuario($conexion, $documento, $nombre_p, $apellido_p, $correo, $clave, $edad, $f_nacimiento, $telefono, $imagen) == 1) {
    echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario registrado exitosamente.']);
} else {
    echo json_encode(['codigo' => 0, 'mensaje' => 'Error: No se pudo registrar el usuario.']);
}



// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().