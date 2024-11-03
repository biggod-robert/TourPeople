<?php

/**
 * Obtiene todos los sitios con la cantidad total de likes y el estado del like dado por el usuario.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_documento ID del usuario para verificar si ha dado like al sitio.
 * @return array|null Retorna un array asociativo con los datos de los sitios o null en caso de error.
 */
function getSitios($conexion, $id_documento)
{
    try {
        // Consulta para obtener los sitios, total de likes y estado de like del usuario
        $sql = "
            SELECT 
                s.id_sitio,
                s.nombre,
                s.descripcion_sitio,
                s.ubi_sitio,
                s.enlace_reservas_turs,
                s.foto,
                s.fecha_creacion,
                COUNT(ls.id_like) AS total_likes,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM tb_like_sitios ls_user
                        WHERE ls_user.id_sitio = s.id_sitio AND ls_user.id_documento = :id_documento
                    ) THEN 'activo'
                    ELSE 'none'
                END AS like_status
            FROM 
                tb_sitios s
            LEFT JOIN 
                tb_like_sitios ls ON s.id_sitio = ls.id_sitio
            GROUP BY 
                s.id_sitio
        ";

        // Preparar y ejecutar la consulta
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
        $ejecutar->execute();

        // Obtener los resultados
        $sitios = $ejecutar->fetchAll(PDO::FETCH_ASSOC);

        return $sitios; // Devuelve el array asociativo con los sitios y su estado de like
    } catch (PDOException $e) {
        error_log("Error en getSitios: " . $e->getMessage());
        return null; // Manejo de errores
    }
}

/**
 * Alterna el estado de "like" en un sitio dado para un usuario específico.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_sitio ID del sitio al cual se le dará o quitará "like".
 * @param int $id_documento ID del usuario que está dando o quitando "like".
 * @param string $like_status Estado actual de "like" ('activo' o 'none').
 * @return bool Retorna true si la operación se realiza con éxito, o false en caso de error.
 */
function toggleLike($conexion, $id_sitio, $id_documento, $like_status)
{
    try {
        // Determina el SQL según el estado de like actual
        if ($like_status !== 'none') {
            // SQL para agregar "like"
            $sql = "INSERT INTO tb_like_sitios (id_sitio, id_documento) VALUES (:id_sitio, :id_documento)";
        } else {
            // SQL para eliminar "like"
            $sql = "DELETE FROM tb_like_sitios WHERE id_sitio = :id_sitio AND id_documento = :id_documento";
        }

        // Preparar la consulta
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_sitio', $id_sitio, PDO::PARAM_INT);
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
 * Obtiene los detalles de un sitio y sus imágenes.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_sitio ID del sitio a obtener.
 * @return array|null Retorna un array con los datos del sitio y sus imágenes o null en caso de error.
 */
function getSitioDetalles($conexion, $id_sitio)
{
    try {
        // Consulta para obtener los detalles del sitio
        $sqlSitio = "
            SELECT 
                s.id_sitio,
                s.nombre,
                s.descripcion_sitio,
                s.ubi_sitio,
                s.enlace_reservas_turs,
                s.foto,
                s.fecha_creacion
            FROM 
                tb_sitios s
            WHERE 
                s.id_sitio = :id_sitio
        ";

        // Preparar y ejecutar la consulta de sitio
        $ejecutarSitio = $conexion->prepare($sqlSitio);
        $ejecutarSitio->bindParam(':id_sitio', $id_sitio, PDO::PARAM_INT);
        $ejecutarSitio->execute();
        $sitio = $ejecutarSitio->fetch(PDO::FETCH_ASSOC);

        if (!$sitio) {
            return null; // Si no se encuentra el sitio, retornar null
        }

        // Consulta para obtener las imágenes del sitio
        $sqlImagenes = "
            SELECT 
                img
            FROM 
                tb_imgSitios
            WHERE 
                id_sitio = :id_sitio
        ";

        // Preparar y ejecutar la consulta de imágenes
        $ejecutarImagenes = $conexion->prepare($sqlImagenes);
        $ejecutarImagenes->bindParam(':id_sitio', $id_sitio, PDO::PARAM_INT);
        $ejecutarImagenes->execute();
        $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_COLUMN);

        // Añadir las imágenes al array del sitio
        $sitio['imagenes'] = $imagenes;

        return $sitio; // Retorna los detalles del sitio con sus imágenes
    } catch (PDOException $e) {
        error_log("Error en getSitioDetalles: " . $e->getMessage());
        return null; // Manejo de errores
    }
}


