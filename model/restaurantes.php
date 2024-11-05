<?php

/**
 * Obtiene todos los restaurantes con la cantidad total de likes y el estado del like dado por el usuario.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_documento ID del usuario para verificar si ha dado like al restaurante.
 * @return array|null Retorna un array asociativo con los datos de los restaurantes o null en caso de error.
 */
function getRestaurantes($conexion, $id_documento)
{
    try {
        // Consulta para obtener los restaurantes, total de likes y estado de like del usuario
        $sql = "
            SELECT 
                r.id_restaurante,
                r.nombre,
                r.descripcion_restaurante,
                r.ubicacion_restaurante,
                r.enlace_reservas_rest,
                r.foto,
                r.fecha_creacion,
                COUNT(lr.id_like) AS total_likes,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM tb_like_restaurantes lr_user
                        WHERE lr_user.id_restaurante = r.id_restaurante AND lr_user.id_documento = :id_documento
                    ) THEN 'activo'
                    ELSE 'none'
                END AS like_status
            FROM 
                tb_restaurantes r
            LEFT JOIN 
                tb_like_restaurantes lr ON r.id_restaurante = lr.id_restaurante
            GROUP BY 
                r.id_restaurante
        ";

        // Preparar y ejecutar la consulta
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
        $ejecutar->execute();

        // Obtener los resultados
        $restaurantes = $ejecutar->fetchAll(PDO::FETCH_ASSOC);

        return $restaurantes; // Devuelve el array asociativo con los restaurantes y su estado de like
    } catch (PDOException $e) {
        error_log("Error en getRestaurantes: " . $e->getMessage());
        return null; // Manejo de errores
    }
}

/**
 * Alterna el estado de "like" en un restaurante dado para un usuario específico.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_restaurante ID del restaurante al cual se le dará o quitará "like".
 * @param int $id_documento ID del usuario que está dando o quitando "like".
 * @param string $like_status Estado actual de "like" ('activo' o 'none').
 * @return bool Retorna true si la operación se realiza con éxito, o false en caso de error.
 */
function toggleLike($conexion, $id_restaurante, $id_documento, $like_status)
{
    try {
        // Determina el SQL según el estado de like actual
        if ($like_status !== 'none') {
            // SQL para agregar "like"
            $sql = "INSERT INTO tb_like_restaurantes (id_restaurante, id_documento) VALUES (:id_restaurante, :id_documento)";
        } else {
            // SQL para eliminar "like"
            $sql = "DELETE FROM tb_like_restaurantes WHERE id_restaurante = :id_restaurante AND id_documento = :id_documento";
        }

        // Preparar la consulta
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_restaurante', $id_restaurante, PDO::PARAM_INT);
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);

        // Ejecutar la consulta y verificar el resultado
        if ($ejecutar->execute()) {
            return true; // Retorna true si la operación es exitosa
        } else {
            return false; // Retorna false si la ejecución falló
        }
    } catch (PDOException $e) {
        error_log("Error en toggleLike: " . $e->getMessage());
        return false; // Manejo de errores
    }
}

/**
 * Obtiene los detalles de un restaurante y sus imágenes.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_restaurante ID del restaurante a obtener.
 * @return array|null Retorna un array con los datos del restaurante y sus imágenes o null en caso de error.
 */
function getRestauranteDetalles($conexion, $id_restaurante)
{
    try {
        // Consulta para obtener los detalles del restaurante
        $sqlRestaurante = "
            SELECT 
                r.id_restaurante,
                r.nombre,
                r.descripcion_restaurante,
                r.ubicacion_restaurante,
                r.enlace_reservas_rest,
                r.foto,
                r.fecha_creacion
            FROM 
                tb_restaurantes r
            WHERE 
                r.id_restaurante = :id_restaurante
        ";

        // Preparar y ejecutar la consulta
        $ejecutarRestaurante = $conexion->prepare($sqlRestaurante);
        $ejecutarRestaurante->bindParam(':id_restaurante', $id_restaurante, PDO::PARAM_INT);
        $ejecutarRestaurante->execute();
        $restaurante = $ejecutarRestaurante->fetch(PDO::FETCH_ASSOC);

        if (!$restaurante) {
            return null; // Si no se encuentra el restaurante, retornar null
        }

        // Consulta para obtener las imágenes del restaurante
        $sqlImagenes = "
            SELECT 
                img
            FROM 
                tb_imgRestaurantes
            WHERE 
                id_restaurante = :id_restaurante
        ";

        // Preparar y ejecutar la consulta de imágenes
        $ejecutarImagenes = $conexion->prepare($sqlImagenes);
        $ejecutarImagenes->bindParam(':id_restaurante', $id_restaurante, PDO::PARAM_INT);
        $ejecutarImagenes->execute();
        $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_COLUMN);

        // Añadir las imágenes al array del restaurante
        $restaurante['imagenes'] = $imagenes;

        return $restaurante; // Retorna los detalles del restaurante con sus imágenes
    } catch (PDOException $e) {
        error_log("Error en getRestauranteDetalles: " . $e->getMessage());
        return null; // Manejo de errores
    }
}

