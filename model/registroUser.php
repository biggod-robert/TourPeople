<?php

// Función para buscar usuario por documento
function buscarUsuarioPorDocumento($conexion, $documento)
{
    try {
        $sql = "SELECT COUNT(*) FROM tb_users WHERE id_documento = :documento";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':documento', $documento, PDO::PARAM_STR);
        $ejecutar->execute();

        return $ejecutar->fetchColumn() > 0 ? 1 : 2; // 1 si existe, 2 si no
    } catch (PDOException $e) {
        error_log("Error en buscarUsuarioPorDocumento: " . $e->getMessage());
        return 0; // Manejo de errores
    }
}

// Función para buscar usuario por correo
function buscarUsuarioPorCorreo($conexion, $correo)
{
    try {
        $sql = "SELECT COUNT(*) FROM tb_users WHERE correo = :correo";
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':correo', $correo, PDO::PARAM_STR);
        $ejecutar->execute();

        return $ejecutar->fetchColumn() > 0 ? 1 : 2; // 1 si existe, 2 si no
    } catch (PDOException $e) {
        error_log("Error en buscarUsuarioPorCorreo: " . $e->getMessage());
        return 0; // Manejo de errores
    }
}

// Función para registrar un nuevo usuario
function registrarUsuario($conexion, $documento, $nombre_p, $apellido_p, $correo, $clave, $edad, $f_nacimiento, $telefono, $imagen)
{
    try {
        // Hash de la contraseña
        $hashedPassword = password_hash($clave, PASSWORD_DEFAULT);

        // Suponiendo que el rol por defecto es 2 (usuario)
        $id_rol = 2;

        // Preparar la consulta SQL para insertar el nuevo usuario
        $sql = "INSERT INTO tb_users (id_documento, id_rol, nombre_p, apellido_p, correo, clave, edad, f_nacimiento, telefono, imagen) 
                VALUES (:id_documento, :id_rol, :nombre_p, :apellido_p, :correo, :clave, :edad, :f_nacimiento, :telefono, :imagen)";

        $ejecutar = $conexion->prepare($sql);

        // Vincular parámetros
        $ejecutar->bindParam(':id_documento', $documento, PDO::PARAM_STR);
        $ejecutar->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        $ejecutar->bindParam(':nombre_p', $nombre_p, PDO::PARAM_STR);
        $ejecutar->bindParam(':apellido_p', $apellido_p, PDO::PARAM_STR);
        $ejecutar->bindParam(':correo', $correo, PDO::PARAM_STR);
        $ejecutar->bindParam(':clave', $hashedPassword, PDO::PARAM_STR);
        $ejecutar->bindParam(':edad', $edad, PDO::PARAM_INT);
        $ejecutar->bindParam(':f_nacimiento', $f_nacimiento, PDO::PARAM_STR);
        $ejecutar->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $ejecutar->bindParam(':imagen', $imagen, PDO::PARAM_STR);

        // Ejecutar la consulta
        return $ejecutar->execute() ? 1 : 0; // Retorna 1 si se ejecutó con éxito, 0 en caso contrario
    } catch (PDOException $e) {
        error_log("Error en registrarUsuario: " . $e->getMessage());
        return 0; // Manejo de errores
    }
}
// Cerrar la conexión a la base de datos
$conexion = null; // Cierra la conexión

// Nota: En PDO, cerrar la conexión se realiza configurando el objeto de conexión a null. 
// No es necesario llamar a un método específico como en MySQLi, donde se usa $conn->close().