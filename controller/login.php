<?php
// Iniciar la sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include("../model/login.php");
include_once("../model/connectionDB.php");

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Obtener datos del usuario desde el formulario
$usuario = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
$clave = htmlspecialchars($_POST['pass'], ENT_QUOTES, 'UTF-8');

// Establecer el número máximo de intentos y tiempo de bloqueo en segundos (2 minutos)
$maxIntentos = 3;
$tiempoBloqueo = 60;

// Comprobar si ya existen las variables de sesión y si el tiempo de bloqueo ha pasado
if (isset($_SESSION['intentos']) && isset($_SESSION['ultimoIntento'])) {
    // Si el usuario está bloqueado y el tiempo de bloqueo no ha expirado, retornar código de bloqueo
    if ($_SESSION['intentos'] >= $maxIntentos && (time() - $_SESSION['ultimoIntento']) < $tiempoBloqueo) {
        echo "0104b"; // Código para indicar que el usuario está bloqueado temporalmente
        exit;
    }

    // Restablecer intentos si el tiempo de bloqueo ha expirado
    if ($_SESSION['intentos'] >= $maxIntentos && (time() - $_SESSION['ultimoIntento']) >= $tiempoBloqueo) {
        $_SESSION['intentos'] = 0;
        $_SESSION['ultimoIntento'] = time();
    }
} else {
    // Inicializar los valores de intentos y último intento si no existen
    $_SESSION['intentos'] = 0;
    $_SESSION['ultimoIntento'] = time();
}

// Verificar usuario y contraseña
if (buscarUsuario($conexion, $usuario) == 1) {
    if (verificarClave($conexion, $usuario, $clave) == 1) {
        // Recuperar datos del usuario y restablecer los intentos
        $_SESSION['user_tour'] = getUser($conexion, $usuario);
        $_SESSION['intentos'] = 0;
switch ($_SESSION['user_tour']['rol']) {
    case 'administrador':
        echo "0101a"; // Código de éxito admin
        break;
    case 'usuario':
        echo "0101u"; // Código de éxito user
        break;
    
    default:
        # code...
        break;
}
        
    } else {
        // Si la contraseña es incorrecta y el usuario no está bloqueado, incrementar intentos
        if ($_SESSION['intentos'] < $maxIntentos) {
            $_SESSION['intentos']++;
            $_SESSION['ultimoIntento'] = time();
        }
        // Enviar respuesta de acuerdo al estado de intentos
        echo ($_SESSION['intentos'] >= $maxIntentos) ? "0104b" : "0102w"; // Código de bloqueo o contraseña incorrecta
    }
} else {
    // Incrementar intentos si el usuario no existe y no está bloqueado
    if ($_SESSION['intentos'] < $maxIntentos) {
        $_SESSION['intentos']++;
        $_SESSION['ultimoIntento'] = time();
    }
    // Enviar respuesta de acuerdo al estado de intentos
    echo ($_SESSION['intentos'] >= $maxIntentos) ? "0104b" : "0103r"; // Código de bloqueo o usuario no encontrado
}

// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().