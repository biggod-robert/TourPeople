<?php

/**
 * Obtiene todos los hoteles con la cantidad total de likes y el estado del like dado por el usuario.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_documento ID del usuario para verificar si ha dado like al hotel.
 * @return array|null Retorna un array asociativo con los datos de los hoteles o null en caso de error.
 */
function getHoteles($conexion, $id_documento)
{
    try {
        // Consulta para obtener los hoteles, total de likes y estado de like del usuario
        $sql = "
            SELECT 
                h.id_hotel,
                h.nombre,
                h.descripcion_hotel,
                h.ubicacion_hotel,
                h.enlace_reservas,
                h.foto,
                h.fecha_creacion,
                COUNT(lh.id_like) AS total_likes,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM tb_like_hoteles lh_user
                        WHERE lh_user.id_hotel = h.id_hotel AND lh_user.id_documento = :id_documento
                    ) THEN 'activo'
                    ELSE 'none'
                END AS like_status
            FROM 
                tb_hoteles h
            LEFT JOIN 
                tb_like_hoteles lh ON h.id_hotel = lh.id_hotel
            GROUP BY 
                h.id_hotel
        ";

        // Preparar y ejecutar la consulta
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
        $ejecutar->execute();

        // Obtener los resultados
        $hoteles = $ejecutar->fetchAll(PDO::FETCH_ASSOC);

        return $hoteles; // Devuelve el array asociativo con los hoteles y su estado de like
    } catch (PDOException $e) {
        error_log("Error en getHoteles: " . $e->getMessage());
        return null; // Manejo de errores
    }
}

/**
 * Alterna el estado de "like" en un hotel dado para un usuario específico.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_hotel ID del hotel al cual se le dará o quitará "like".
 * @param int $id_documento ID del usuario que está dando o quitando "like".
 * @param string $like_status Estado actual de "like" ('activo' o 'none').
 * @return bool Retorna true si la operación se realiza con éxito, o false en caso de error.
 */
function toggleLike($conexion, $id_hotel, $id_documento, $like_status)
{
    try {
        // Determina el SQL según el estado de like actual
        if ($like_status !== 'none') {
            // SQL para agregar "like"
            $sql = "INSERT INTO tb_like_hoteles (id_hotel, id_documento) VALUES (:id_hotel, :id_documento)";
        } else {
            // SQL para eliminar "like"
            $sql = "DELETE FROM tb_like_hoteles WHERE id_hotel = :id_hotel AND id_documento = :id_documento";
        }

        // Preparar la consulta
        $ejecutar = $conexion->prepare($sql);
        $ejecutar->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
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
 * Obtiene los detalles de un hotel y sus imágenes.
 *
 * @param PDO $conexion Conexión a la base de datos.
 * @param int $id_hotel ID del hotel a obtener.
 * @return array|null Retorna un array con los datos del hotel y sus imágenes o null en caso de error.
 */
function getHotelDetalles($conexion, $id_hotel)
{
    try {
        // Consulta para obtener los detalles del hotel
        $sqlHotel = "
            SELECT 
                h.id_hotel,
                h.nombre,
                h.descripcion_hotel,
                h.ubicacion_hotel,
                h.enlace_reservas,
                h.foto,
                h.fecha_creacion
            FROM 
                tb_hoteles h
            WHERE 
                h.id_hotel = :id_hotel
        ";

        // Preparar y ejecutar la consulta de hotel
        $ejecutarHotel = $conexion->prepare($sqlHotel);
        $ejecutarHotel->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
        $ejecutarHotel->execute();
        $hotel = $ejecutarHotel->fetch(PDO::FETCH_ASSOC);

        if (!$hotel) {
            return null; // Si no se encuentra el hotel, retornar null
        }

        // Consulta para obtener las imágenes del hotel
        $sqlImagenes = "
            SELECT 
                img
            FROM 
                tb_imgHoteles
            WHERE 
                id_hotel = :id_hotel
        ";

        // Preparar y ejecutar la consulta de imágenes
        $ejecutarImagenes = $conexion->prepare($sqlImagenes);
        $ejecutarImagenes->bindParam(':id_hotel', $id_hotel, PDO::PARAM_INT);
        $ejecutarImagenes->execute();
        $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_COLUMN);

        // Añadir las imágenes al array del hotel
        $hotel['imagenes'] = $imagenes;

        return $hotel; // Retorna los detalles del hotel con sus imágenes
    } catch (PDOException $e) {
        error_log("Error en getHotelDetalles: " . $e->getMessage());
        return null; // Manejo de errores
    }
}
