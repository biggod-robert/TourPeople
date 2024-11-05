<?php

/**
 * Realiza operaciones CRUD para la administración de restaurantes.
 *
 * @param PDO $conexion Conexión activa a la base de datos.
 * @param int $opcion Define la operación a realizar:
 *                    1: Obtener restaurantes por usuario.
 *                    2: Insertar nuevo restaurante.
 *                    3: Obtener restaurante por ID.
 *                    4: Eliminar imagen asociada a un restaurante.
 *                    5: Actualizar datos de un restaurante existente.
 *                    6: Eliminar un restaurante y sus imágenes.
 * @param int $id_documento ID del documento que referencia al usuario.
 * @param string $nombreRestaurante Nombre del restaurante.
 * @param string $descripcion Descripción del restaurante.
 * @param array $imgPortada Imagen de portada del restaurante.
 * @param string $ubicacion Ubicación del restaurante.
 * @param string $enlace_reservas URL para reservas del restaurante.
 * @param array $imgRestaurante Imágenes adicionales del restaurante.
 * @param int $idRestaurante ID del restaurante a recuperar o editar.
 * @param int $idImagen ID de la imagen a eliminar.
 * @param int $id_restauranteEdit ID del restaurante a actualizar.
 * @param int $idRestauranteDelete ID del restaurante a eliminar.
 * 
 * @return array Resultado de la operación con códigos y mensajes.
 */
function restaurantes($conexion, $opcion, $id_documento, $nombreRestaurante, $descripcion, $imgPortada, $ubicacion, $enlace_reservas, $imgRestaurante, $idRestaurante, $idImagen, $id_restauranteEdit, $idRestauranteDelete) {
    $salida = "";
    switch ($opcion) {
        case 1:
            // Obtener restaurantes del usuario
            $sql = "SELECT * FROM tb_restaurantes WHERE id_documento = :id_documento";
            $ejecutar = $conexion->prepare($sql);
            $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
            $ejecutar->execute();
            $salida = $ejecutar->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 2:
            // Insertar nuevo restaurante
            $imageFileType = strtolower(pathinfo($imgPortada['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid('img_', true) . '.' . $imageFileType;
            $target_dir_portada = "../upload/restaurantes/portadas/";
            $target_file_portada = $target_dir_portada . $newFileName;

            if (move_uploaded_file($imgPortada['tmp_name'], $target_file_portada)) {
                // Insertar restaurante
                $sql = "INSERT INTO `tb_restaurantes` (`nombre`, `descripcion_restaurante`, `ubi_restaurante`, `enlace_reservas_rest`, `foto`, `id_documento`) 
                VALUES (:nombre, :descripcion, :ubicacion, :enlace_reservas, :imgPortada, :id_documento)";
                $ejecutar = $conexion->prepare($sql);
                $ejecutar->bindParam(':nombre', $nombreRestaurante, PDO::PARAM_STR);
                $ejecutar->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $ejecutar->bindParam(':imgPortada', $newFileName, PDO::PARAM_STR);
                $ejecutar->bindParam(':ubicacion', $ubicacion, PDO::PARAM_STR);
                $ejecutar->bindParam(':enlace_reservas', $enlace_reservas, PDO::PARAM_STR);
                $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
                $ejecutar->execute();

                // Obtener el ID del último restaurante insertado
                $id_restaurante = $conexion->lastInsertId();

                // Procesar imágenes adicionales
                if (!empty($imgRestaurante) && isset($imgRestaurante['name'])) {
                    $filesCount = count($imgRestaurante['name']);
                    $target_dir_img = "../upload/restaurantes/images/";

                    for ($i = 0; $i < $filesCount; $i++) {
                        if ($imgRestaurante['error'][$i] == UPLOAD_ERR_OK) {
                            $imgName = $imgRestaurante['name'][$i];
                            $imageFileType = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                            $newFileName = uniqid('img_', true) . '.' . $imageFileType;
                            $target_file_img = $target_dir_img . $newFileName;

                            if (move_uploaded_file($imgRestaurante['tmp_name'][$i], $target_file_img)) {
                                $sql = "INSERT INTO tb_imgRestaurantes (img, id_restaurante) VALUES (:img, :id_restaurante)";
                                $ejecutar = $conexion->prepare($sql);
                                $ejecutar->bindParam(':img', $newFileName, PDO::PARAM_STR);
                                $ejecutar->bindParam(':id_restaurante', $id_restaurante, PDO::PARAM_INT);
                                $ejecutar->execute();
                            }
                        }
                    }
                }

                $salida = array("codigo" => 1, "mensaje" => "Restaurante agregado correctamente.");
            } else {
                $salida = array("codigo" => 0, "mensaje" => "Error al subir la imagen de portada.");
            }
            break;

        case 3:
            // Obtener restaurante por ID
            $sqlRestaurante = "SELECT * FROM tb_restaurantes WHERE id_restaurante = :idRestaurante";
            $ejecutarRestaurante = $conexion->prepare($sqlRestaurante);
            $ejecutarRestaurante->bindParam(':idRestaurante', $idRestaurante, PDO::PARAM_INT);
            $ejecutarRestaurante->execute();
            $restaurante = $ejecutarRestaurante->fetch(PDO::FETCH_ASSOC);

            if ($restaurante) {
                // Obtener imágenes asociadas
                $sqlImagenes = "SELECT id_img, img FROM tb_imgRestaurantes WHERE id_restaurante = :idRestaurante";
                $ejecutarImagenes = $conexion->prepare($sqlImagenes);
                $ejecutarImagenes->bindParam(':idRestaurante', $idRestaurante, PDO::PARAM_INT);
                $ejecutarImagenes->execute();
                $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_ASSOC);

                $restauranteConImagenes = array(
                    "id_restaurante" => $restaurante['id_restaurante'],
                    "nombre" => $restaurante['nombre'],
                    "descripcion" => $restaurante['descripcion_restaurante'],
                    "imgPortada" => $restaurante['foto'],
                    "ubi_restaurante" => $restaurante['ubi_restaurante'],
                    "enlace_reservas_rest" => $restaurante['enlace_reservas_rest'],
                    "imagenes" => $imagenes
                );

                $salida = array("codigo" => 1, "data" => $restauranteConImagenes);
            } else {
                $salida = array("codigo" => 0, "mensaje" => "Restaurante no encontrado.");
            }
            break;

        case 4:
            // Eliminar imagen por ID
            if ($idImagen !== null) {
                $sqlImg = "SELECT img FROM tb_imgRestaurantes WHERE id_img = :idImagen";
                $ejecutarImg = $conexion->prepare($sqlImg);
                $ejecutarImg->bindParam(':idImagen', $idImagen, PDO::PARAM_INT);
                $ejecutarImg->execute();
                $imagen = $ejecutarImg->fetch(PDO::FETCH_ASSOC);

                if ($imagen) {
                    $rutaImagen = "../upload/restaurantes/images/" . $imagen['img'];
                    
                    $sqlDelete = "DELETE FROM tb_imgRestaurantes WHERE id_img = :idImagen";
                    $ejecutarDelete = $conexion->prepare($sqlDelete);
                    $ejecutarDelete->bindParam(':idImagen', $idImagen, PDO::PARAM_INT);
                    $ejecutarDelete->execute();

                    if ($ejecutarDelete->rowCount() > 0) {
                        if (file_exists($rutaImagen)) {
                            if (unlink($rutaImagen)) {
                                $salida = array("codigo" => 1, "mensaje" => "Imagen eliminada correctamente.");
                            } else {
                                $salida = array("codigo" => 0, "mensaje" => "Error al eliminar el archivo de imagen.");
                            }
                        } else {
                            $salida = array("codigo" => 0, "mensaje" => "El archivo de imagen no existe.");
                        }
                    } else {
                        $salida = array("codigo" => 0, "mensaje" => "Error al eliminar la imagen de la base de datos.");
                    }
                } else {
                    $salida = array("codigo" => 0, "mensaje" => "Imagen no encontrada.");
                }
            } else {
                $salida = array("codigo" => 0, "mensaje" => "ID de imagen no proporcionado.");
            }
            break;

        case 5:
            // Actualizar restaurante existente
            if (!empty($id_restauranteEdit)) {
                $sql = "UPDATE tb_restaurantes SET nombre = :nombre, descripcion_restaurante = :descripcion, 
                        ubi_restaurante = :ubicacion, enlace_reservas_rest = :enlace_reservas";

                if (!empty($imgPortada['name'])) {
                    $imageFileType = strtolower(pathinfo($imgPortada['name'], PATHINFO_EXTENSION));
                    $newFileName = uniqid('img_', true) . '.' . $imageFileType;
                    $target_dir_portada = "../upload/restaurantes/portadas/";
                    $target_file_portada = $target_dir_portada . $newFileName;

                    if (move_uploaded_file($imgPortada['tmp_name'], $target_file_portada)) {
                        $sql .= ", foto = :imgPortada";
                    } else {
                        $salida = array("codigo" => 0, "mensaje" => "Error al subir la nueva imagen de portada.");
                        break;
                    }
                }

                $sql .= " WHERE id_restaurante = :id_restauranteEdit";
                $ejecutar = $conexion->prepare($sql);
                $ejecutar->bindParam(':nombre', $nombreRestaurante, PDO::PARAM_STR);
                $ejecutar->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $ejecutar->bindParam(':ubicacion', $ubicacion, PDO::PARAM_STR);
                $ejecutar->bindParam(':enlace_reservas', $enlace_reservas, PDO::PARAM_STR);

                if (!empty($imgPortada['name'])) {
                    $ejecutar->bindParam(':imgPortada', $newFileName, PDO::PARAM_STR);
                }

                $ejecutar->bindParam(':id_restauranteEdit', $id_restauranteEdit, PDO::PARAM_INT);
                $ejecutar->execute();

                // Procesar imágenes adicionales
                if (!empty($imgRestaurante) && isset($imgRestaurante['name']) && count($imgRestaurante['name']) > 0) {
                    $filesCount = count($imgRestaurante['name']);
                    $target_dir_img = "../upload/restaurantes/images/";

                    for ($i = 0; $i < $filesCount; $i++) {
                        if ($imgRestaurante['error'][$i] == UPLOAD_ERR_OK) {
                            $imgName = $imgRestaurante['name'][$i];
                            $imageFileType = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                            $newFileName = uniqid('img_', true) . '.' . $imageFileType;
                            $target_file_img = $target_dir_img . $newFileName;

                            if (move_uploaded_file($imgRestaurante['tmp_name'][$i], $target_file_img)) {
                                $sql = "INSERT INTO tb_imgRestaurantes (img, id_restaurante) VALUES (:img, :id_restaurante)";
                                $ejecutar = $conexion->prepare($sql);
                                $ejecutar->bindParam(':img', $newFileName, PDO::PARAM_STR);
                                $ejecutar->bindParam(':id_restaurante', $id_restauranteEdit, PDO::PARAM_INT);
                                $ejecutar->execute();
                            }
                        }
                    }
                }

                $salida = array("codigo" => 1, "mensaje" => "Restaurante actualizado correctamente.");
            } else {
                $salida = array("codigo" => 0, "mensaje" => "ID de restaurante no proporcionado.");
            }
            break;

            case 6:
                // Eliminar restaurante por ID
                if (!empty($idRestauranteDelete)) {
                    // Primero, obtener el restaurante y sus imágenes asociadas
                    $sqlRestaurante = "SELECT foto FROM tb_restaurantes WHERE id_restaurante = :idRestaurante";
                    $ejecutarRestaurante = $conexion->prepare($sqlRestaurante);
                    $ejecutarRestaurante->bindParam(':idRestaurante', $idRestauranteDelete, PDO::PARAM_INT);
                    $ejecutarRestaurante->execute();
                    $restaurante = $ejecutarRestaurante->fetch(PDO::FETCH_ASSOC);
            
                    if ($restaurante) {
                        // Iniciar transacción para asegurar la integridad de los datos
                        $conexion->beginTransaction();
            
                        try {
                            // Eliminar la imagen de portada del servidor
                            $rutaPortada = "../upload/restaurantes/portadas/" . $restaurante['foto'];
                            if (file_exists($rutaPortada)) {
                                unlink($rutaPortada);
                            }
            
                            // Obtener y eliminar imágenes adicionales
                            $sqlImagenes = "SELECT img FROM tb_imgRestaurantes WHERE id_restaurante = :idRestaurante";
                            $ejecutarImagenes = $conexion->prepare($sqlImagenes);
                            $ejecutarImagenes->bindParam(':idRestaurante', $idRestauranteDelete, PDO::PARAM_INT);
                            $ejecutarImagenes->execute();
                            $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_ASSOC);
            
                            // Eliminar archivos físicos de imágenes adicionales
                            foreach ($imagenes as $img) {
                                $rutaImagen = "../upload/restaurantes/images/" . $img['img'];
                                if (file_exists($rutaImagen)) {
                                    unlink($rutaImagen);
                                }
                            }
            
                            // Eliminar likes asociados
                            $sqlDeleteLikes = "DELETE FROM tb_like_restaurantes WHERE id_restaurante = :idRestaurante";
                            $ejecutarDeleteLikes = $conexion->prepare($sqlDeleteLikes);
                            $ejecutarDeleteLikes->bindParam(':idRestaurante', $idRestauranteDelete, PDO::PARAM_INT);
                            $ejecutarDeleteLikes->execute();
            
                            // Eliminar imágenes de la base de datos
                            $sqlDeleteImagenes = "DELETE FROM tb_imgRestaurantes WHERE id_restaurante = :idRestaurante";
                            $ejecutarDeleteImagenes = $conexion->prepare($sqlDeleteImagenes);
                            $ejecutarDeleteImagenes->bindParam(':idRestaurante', $idRestauranteDelete, PDO::PARAM_INT);
                            $ejecutarDeleteImagenes->execute();
            
                            // Finalmente, eliminar el restaurante
                            $sqlDeleteRestaurante = "DELETE FROM tb_restaurantes WHERE id_restaurante = :idRestaurante";
                            $ejecutarDeleteRestaurante = $conexion->prepare($sqlDeleteRestaurante);
                            $ejecutarDeleteRestaurante->bindParam(':idRestaurante', $idRestauranteDelete, PDO::PARAM_INT);
                            $ejecutarDeleteRestaurante->execute();
            
                            // Confirmar la transacción
                            $conexion->commit();
                            $salida = array("codigo" => 1, "mensaje" => "Restaurante eliminado correctamente.");
                        } catch (Exception $e) {
                            // Si algo sale mal, revertir todos los cambios
                            $conexion->rollBack();
                            $salida = array("codigo" => 0, "mensaje" => "Error al eliminar el restaurante: " . $e->getMessage());
                        }
                    } else {
                        $salida = array("codigo" => 0, "mensaje" => "Restaurante no encontrado.");
                    }
                } else {
                    $salida = array("codigo" => 0, "mensaje" => "ID de restaurante no proporcionado.");
                }
                break;
    }

    return $salida;
}

