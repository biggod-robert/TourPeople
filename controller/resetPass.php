<?php
// Incluir archivos necesarios
include("../model/login.php"); // se incluye el modelo del login para reutilizar funciones como buscarUsuario
include_once("../model/connectionDB.php");

// Incluir la libreria PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../assets/PHPMailer/Exception.php';
require '../assets/PHPMailer/PHPMailer.php';
require '../assets/PHPMailer/SMTP.php';

// Crear conexión a la base de datos
$objeto = new Connection();
$conexion = $objeto->Conectar();

// Obtener datos del usuario desde el formulario
$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

// Verificar correo
if (buscarUsuario($conexion, $correo) == 1) {
    // Si se encuentra el correo, se obtiene el ID del usuario
    $idUsuario = getUserIdByEmail($conexion, $correo);

    // Generar un código único de 6 dígitos aleatorio entre letras y números
    $codigo = generateRandomCode(6);

    // Guardar el código en la base de datos
    if (saveCodeResetPass($conexion, $idUsuario, $codigo) == 1) {
        // Si se guarda con éxito el código, enviar el código al correo registrado del usuario
        if (sendCodeByEmail($correo, $codigo)) {
            echo json_encode(['codigo' => 1, 'mensaje' => 'Código de validación enviado a ' . $correo . '. <br>Verifica tu bandeja de entrada y spam.']);
        } else {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al enviar el código por correo.']);
        }
    } else {
        echo json_encode(['codigo' => 0, 'mensaje' => 'Error: error al generar el codigo de validaciòn, intentalo nuevamente.']);
    }
} else {
    echo json_encode(['codigo' => 0, 'mensaje' => 'Error: Usuario no encontrado.']);
}

// Función para enviar el código por correo
function sendCodeByEmail($correo, $codigo)
{
    // Inicio
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP (ajusta según tu entorno)
        $mail->isSMTP(); // Activar envío SMTP

        // Configuración para localhost
        $mail->Host  = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth  = true; // Identificación SMTP
        $mail->Username  = 'robertmoor2003@gmail.com'; // Usuario SMTP
        $mail->Password  = ''; // Contraseña de aplicación generada
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar cifrado TLS
        $mail->Port  = 587; // Puerto para TLS

        $mail->setFrom('robertmoor2003@gmail.com', 'TourPeople'); // Remitente del correo

        // Destinatarios
        $mail->addAddress($correo, 'Nombre del destinatario'); // Email y nombre del destinatario

        // Contenido del correo
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8'; // Establecer la codificación de caracteres
        $mail->Subject = 'Código de Restablecimiento de Contraseña';
        $mail->Body  = 'Tu código de validación es: <strong>' . $codigo . '</strong>';
        $mail->AltBody = 'Tu código de validación es: ' . $codigo; // Texto alternativo para clientes de correo que no soportan HTML

        // Enviar el correo
        $mail->send();
        return true; // Mensaje enviado exitosamente
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false; // Error al enviar el mensaje
    }
}

// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().