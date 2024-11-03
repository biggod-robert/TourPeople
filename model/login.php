<?php

function buscarUsuario($conexion, $usuario)
{
    try {
        $sql = "SELECT buscarUsuario(:usuario) as resultado";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $ejecutar->execute();
        $salida = $ejecutar->fetch(PDO::FETCH_ASSOC);
        return $salida['resultado'];
    } catch (PDOException $e) {
        // Manejo de errores
        error_log("Error en buscarUsuario: " . $e->getMessage());
        return 0;
    }
}

function verificarClave($conexion, $usuario, $clave)
{
    try {
        $sql = "SELECT buscarClave(:usuario) as resultado";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $ejecutar->execute();
        $claveRe = $ejecutar->fetch(PDO::FETCH_ASSOC);
        $claveDB = $claveRe['resultado'];

        return password_verify($clave, $claveDB) ? 1 : 0;
    } catch (PDOException $e) {
        // Manejo de errores
        error_log("Error en verificarClave: " . $e->getMessage());
        return 0;
    }
}

function getUser($conexion, $usuario)
{
    try {
        // Obtener el ID del usuario a partir del correo
        $sql = "SELECT id_documento FROM tb_users WHERE correo = :correo";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':correo', $usuario, PDO::PARAM_STR);
        $ejecutar->execute();

        $resultado = $ejecutar->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró un usuario con ese correo
        if ($resultado) {
            $id_user = $resultado['id_documento'];

            // Consultar todos los datos del usuario junto con su rol
            $sql = "SELECT 
            u.id_documento, 
            u.nombre_p, 
            u.apellido_p, 
            u.correo, 
            u.clave, 
            u.edad, 
            u.f_nacimiento, 
            u.telefono, 
            u.imagen, 
            u.fecha_ingreso, 
            r.rol 
        FROM 
            tb_users u
        INNER JOIN 
            tb_roles r ON u.id_rol = r.id_rol
        WHERE 
            u.id_documento = :id_user";

            $ejecutar = $conexion->prepare($sql);
            $ejecutar->bindParam(':id_user', $id_user, PDO::PARAM_STR);
            $ejecutar->execute();

            $salida = $ejecutar->fetch(PDO::FETCH_ASSOC);
            return $salida;
        } else {
            return null; // Retorna null si no se encuentra el correo
        }
    } catch (PDOException $e) {
        // Manejo de errores
        error_log("Error en getUser: " . $e->getMessage());
        return null;
    }
}

// estas funciones complementan el reastablecimiento de ontraseña
// Función para obtener el ID del usuario por correo
function getUserIdByEmail($conexion, $correo)
{
    try {
        $sql = "SELECT id_documento FROM tb_users WHERE correo = :correo";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':correo', $correo, PDO::PARAM_STR);
        $ejecutar->execute();
        $resultado = $ejecutar->fetch(PDO::FETCH_ASSOC);
        return $resultado['id_documento'] ?? null; // Devuelve el ID o null si no se encuentra
    } catch (PDOException $e) {
        error_log("Error en getUserIdByEmail: " . $e->getMessage());
        return null; // Manejo de errores
    }
}

// Función para generar un código aleatorio
function generateRandomCode($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $code;
}

// Función para guardar el código de restablecimiento de contraseña
function saveCodeResetPass($conexion, $idUsuario, $codigo)
{
    try {
        // Primero, eliminar el código existente si hay uno para el usuario
        $sqlDelete = "DELETE FROM tb_code_reset_pass WHERE id_documento = :id_documento";
        $ejecutarDelete = $conexion->prepare($sqlDelete);
        $ejecutarDelete->bindParam(':id_documento', $idUsuario, PDO::PARAM_INT);
        $ejecutarDelete->execute();

        // Ahora, insertar el nuevo código
        $sqlInsert = "INSERT INTO tb_code_reset_pass (code, id_documento) VALUES (:codigo, :id_documento)";
        $ejecutarInsert = $conexion->prepare($sqlInsert);
        $ejecutarInsert->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $ejecutarInsert->bindParam(':id_documento', $idUsuario, PDO::PARAM_INT);
        $ejecutarInsert->execute();

        return 1; // Código guardado exitosamente
    } catch (PDOException $e) {
        error_log("Error en saveCodeResetPass: " . $e->getMessage());
        return 0; // Manejo de errores
    }
}

// Función para validar el código de restablecimiento de clave
function validarCodigo($conexion, $id_documento, $codeUser)
{
    try {
        // Primera validación: Comprobar que el código en la base de datos no esté vacío
        $sql = "SELECT code FROM tb_code_reset_pass WHERE id_documento = :id_documento";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
        $ejecutar->execute();

        // Obtiene el resultado de la consulta
        $resultado = $ejecutar->fetch(PDO::FETCH_ASSOC);

        if (!$resultado || empty($resultado['code'])) {
            return 0;
        }

        // Segunda validación: Comparar el código de la base de datos con el proporcionado por el usuario
        if (strcmp($resultado['code'], $codeUser) === 0) {
            return 1;
        } else {
            return 0;
        }
    } catch (PDOException $e) {
        error_log("Error en validarCodigo: " . $e->getMessage());
        return 0;
    }
}

// Función para restablecer contraseña
function restablecerPassword($conexion, $id_documento, $newPassword)
{
    try {
        // Hash de la nueva contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Preparar la consulta SQL para actualizar la contraseña
        $sql = "UPDATE tb_users SET clave = :newPass WHERE id_documento = :id_documento";
        $ejecutar = $conexion->prepare($sql);

        // Vincular parámetros
        $ejecutar->bindParam(':newPass', $hashedPassword, PDO::PARAM_STR);
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_STR);

        // Ejecutar la consulta
        return $ejecutar->execute() ? 1 : 0; // Retorna 1 si se ejecutó con éxito, 0 en caso contrario
    } catch (PDOException $e) {
        error_log("Error en restablecerPassword: " . $e->getMessage());
        return 0; // Manejo de errores
    }
}
