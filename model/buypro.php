<?php

/**
 * Función para actualizar el rol de un usuario a "Administrador"
 * 
 * @param PDO $conexion La conexión a la base de datos mediante PDO.
 * @param int $id_documento El ID del documento del usuario a actualizar.
 * 
 * @return int Retorna 1 si la actualización fue exitosa, 0 si hubo algún problema.
 */
function updateUserToAdmin($conexion, $id_documento)
{
    try {
        // Definición de la consulta SQL para actualizar el rol del usuario
        $sql = "UPDATE tb_users SET id_rol = 1 WHERE id_documento = :id_documento";

        // Preparación de la consulta
        $ejecutar = $conexion->prepare($sql);

        // Vinculación del parámetro
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);

        // Ejecución de la consulta
        if ($ejecutar->execute()) {
            // Retorna 1 si la actualización fue exitosa
            return 1;
        } else {
            // Retorna 0 si hubo algún problema durante la ejecución
            return 0;
        }
    } catch (PDOException $e) {
        // Registrar el error en el archivo de registro
        error_log("Error en updateUserToAdmin: " . $e->getMessage());

        // Retorna 0 en caso de excepción
        return 0;
    }
}
