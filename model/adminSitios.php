<?php

/**
 * Realiza operaciones CRUD para la administración de sitios.
 *
 * @param PDO $conexion Conexión activa a la base de datos.
 * @param int $opcion Define la operación a realizar:
 *                    1: Obtener sitios por usuario.
 *                    2: Insertar nuevo sitio.
 *                    3: Obtener sitio por ID.
 *                    4: Eliminar imagen asociada a un sitio.
 *                    5: Actualizar datos de un sitio existente.
 *                    6: Eliminar un sitio y sus imágenes.
 * @param int $id_documento ID del documento que referencia al usuario.
 * @param string $nombreSitio Nombre del sitio.
 * @param string $descripcion Descripción del sitio.
 * @param array $imgPortada Imagen de portada del sitio.
 * @param string $direccion Dirección del sitio.
 * @param string $enlace_reservas URL para reservas del sitio.
 * @param array $imgSitio Imágenes adicionales del sitio.
 * @param int $idsitio ID del sitio a recuperar o editar.
 * @param int $idImagen ID de la imagen a eliminar.
 * @param int $id_sitioEdit ID del sitio a actualizar.
 * @param int $idsitioDelete ID del sitio a eliminar.
 * 
 * @return array Resultado de la operación con códigos y mensajes.
 */
function sitios($conexion, $opcion, $id_documento, $nombreSitio, $descripcion, $imgPortada, $direccion, $enlace_reservas, $imgSitio, $idsitio, $idImagen, $id_sitioEdit, $idsitioDelete)
{
    $salida = "";
    switch ($opcion) {
        case 1:
            // Obtener sitios subidos por el usuario
            $sql = "SELECT * FROM tb_sitios WHERE id_documento = :id_documento";
            $ejecutar = $conexion->prepare($sql);
            $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
            $ejecutar->execute();
            $salida = $ejecutar->fetchAll(PDO::FETCH_ASSOC);

            break;

        case 2:
            // Se inserta una nuevo sitio
            // Procesar primero la imagen de portada
            $imageFileType = strtolower(pathinfo($imgPortada['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid('img_', true) . '.' . $imageFileType;
            $target_dir_portada = "../upload/sitios/portadas/";
            $target_file_portada = $target_dir_portada . $newFileName;

            if (move_uploaded_file($imgPortada['tmp_name'], $target_file_portada)) {
                // Insertar sitio
                $sql = "INSERT INTO `tb_sitios` (`id_sitio`, `nombre`, `descripcion_sitio`, `ubi_sitio`, `enlace_reservas_turs`, `foto`, `id_documento`) 
                VALUES (NULL, :nombre, :descripcion, :direccion, :enlace_reservas, :imgPortada, :id_documento);";
                $ejecutar = $conexion->prepare($sql);
                $ejecutar->bindParam(':nombre', $nombreSitio, PDO::PARAM_STR);
                $ejecutar->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $ejecutar->bindParam(':imgPortada', $newFileName, PDO::PARAM_STR);
                $ejecutar->bindParam(':direccion', $direccion, PDO::PARAM_STR);
                $ejecutar->bindParam(':enlace_reservas', $enlace_reservas, PDO::PARAM_STR);
                $ejecutar->bindParam(':id_documento', $id_documento, PDO::PARAM_INT);
                $ejecutar->execute();


                // Obtener el ID de la última sitio insertada
                $id_sitio = $conexion->lastInsertId();

                // Procesar imágenes adicionales
                if (!empty($imgSitio) && isset($imgSitio['name'])) {
                    $filesCount = count($imgSitio['name']);
                    $target_dir_img = "../upload/sitios/images/";

                    for ($i = 0; $i < $filesCount; $i++) {
                        if ($imgSitio['error'][$i] == UPLOAD_ERR_OK) {
                            // Generar un nombre único para cada imagen adicional
                            $imgName = $imgSitio['name'][$i];
                            $imageFileType = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                            $newFileName = uniqid('img_', true) . '.' . $imageFileType;
                            $target_file_img = $target_dir_img . $newFileName;

                            // Mover el archivo a la carpeta de destino para imágenes adicionales
                            if (move_uploaded_file($imgSitio['tmp_name'][$i], $target_file_img)) {
                                // Insertar el nombre de la imagen en la tabla tb_imgSitios
                                $sql = "INSERT INTO tb_imgSitios (img, id_sitio) VALUES (:img, :id_sitio)";
                                $ejecutar = $conexion->prepare($sql);
                                $ejecutar->bindParam(':img', $newFileName, PDO::PARAM_STR);
                                $ejecutar->bindParam(':id_sitio', $id_sitio, PDO::PARAM_INT);
                                $ejecutar->execute();
                            } else {
                                error_log("Error al mover la imagen adicional: " . print_r(error_get_last(), true));
                            }
                        } else {
                            error_log("Error en la subida de imagen adicional: " . $imgSitio['error'][$i]);
                        }
                    }
                }

                $salida = array("codigo" => 1, "mensaje" => "Se agrego el sitio correctamente.");
            } else {
                $salida = array("codigo" => 0, "mensaje" => "Error al subir la imagen de portada. Error: " . print_r(error_get_last(), true));
            }
            break;

        case 3:
            // Obtener un sitio por su ID
            $sqlsitio = "SELECT * FROM tb_sitios WHERE id_sitio = :idsitio";
            $ejecutarsitio = $conexion->prepare($sqlsitio);
            $ejecutarsitio->bindParam(':idsitio', $idsitio, PDO::PARAM_INT);
            $ejecutarsitio->execute();
            $sitio = $ejecutarsitio->fetch(PDO::FETCH_ASSOC); // Solo una sitio, no fetchAll()

            if ($sitio) {
                // Si la sitio existe, obtener las imágenes asociadas
                $sqlImagenes = "SELECT id_img, img FROM tb_imgSitios WHERE id_sitio = :idsitio";
                $ejecutarImagenes = $conexion->prepare($sqlImagenes);
                $ejecutarImagenes->bindParam(':idsitio', $idsitio, PDO::PARAM_INT);
                $ejecutarImagenes->execute();
                $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_ASSOC); // Traemos todas las imágenes

                // Organizar los resultados en un array
                $sitioConImagenes = array(
                    "id_sitio" => $sitio['id_sitio'],
                    "nombre" => $sitio['nombre'],
                    "descripcion" => $sitio['descripcion_sitio'],
                    "imgPortada" => $sitio['foto'],
                    "ubi_sitio" => $sitio['ubi_sitio'],
                    "enlace_reservas_turs" => $sitio['enlace_reservas_turs'],
                    "imagenes" => $imagenes // Las imágenes se almacenan como un subarray
                );

                $salida = array("codigo" => 1, "data" => $sitioConImagenes);
            } else {
                $salida = array("codigo" => 0, "mensaje" => "sitio no encontrada.");
            }
            break;

        case 4:
            // Eliminar imagen por ID
            if ($idImagen !== null) {
                // Primero, obtener la ruta de la imagen desde la base de datos
                $sqlImg = "SELECT img FROM tb_imgSitios WHERE id_img = :idImagen";
                $ejecutarImg = $conexion->prepare($sqlImg);
                $ejecutarImg->bindParam(':idImagen', $idImagen, PDO::PARAM_INT);
                $ejecutarImg->execute();
                $imagen = $ejecutarImg->fetch(PDO::FETCH_ASSOC);

                if ($imagen) {
                    // Ruta de la imagen en el sistema de archivos
                    $rutaImagen = "../upload/sitios/images/" . $imagen['img'];

                    // Eliminar la imagen de la base de datos
                    $sqlDelete = "DELETE FROM tb_imgSitios WHERE id_img = :idImagen";
                    $ejecutarDelete = $conexion->prepare($sqlDelete);
                    $ejecutarDelete->bindParam(':idImagen', $idImagen, PDO::PARAM_INT);
                    $ejecutarDelete->execute();

                    // Verificar si la imagen fue eliminada de la base de datos
                    if ($ejecutarDelete->rowCount() > 0) {
                        // Intentar eliminar el archivo físico
                        if (file_exists($rutaImagen)) {
                            if (unlink($rutaImagen)) {
                                // Si se eliminó correctamente
                                $salida = array("codigo" => 1, "mensaje" => "Imagen eliminada correctamente.");
                            } else {
                                // Error al eliminar el archivo físico
                                $salida = array("codigo" => 0, "mensaje" => "Error al eliminar el archivo de imagen.");
                            }
                        } else {
                            // Archivo no existe en el sistema de archivos
                            $salida = array("codigo" => 0, "mensaje" => "El archivo de imagen no existe.");
                        }
                    } else {
                        // Error al eliminar el registro en la base de datos
                        $salida = array("codigo" => 0, "mensaje" => "Error al eliminar la imagen de la base de datos.");
                    }
                } else {
                    // No se encontró la imagen en la base de datos
                    $salida = array("codigo" => 0, "mensaje" => "Imagen no encontrada.");
                }
            } else {
                // Si no se recibe un ID de imagen válido
                $salida = array("codigo" => 0, "mensaje" => "ID de imagen no proporcionado.");
            }
            break;
        case 5:
            // Actualizar sitio existente
            if (!empty($id_sitioEdit)) {
                // Consulta base para la actualización del sitio
                $sql = "UPDATE tb_sitios SET nombre = :nombre, descripcion_sitio = :descripcion, ubi_sitio = :direccion, enlace_reservas_turs = :enlace_reservas";

                // Si hay una nueva imagen de portada, agregarla a la consulta
                if (!empty($imgPortada['name'])) {
                    $imageFileType = strtolower(pathinfo($imgPortada['name'], PATHINFO_EXTENSION));
                    $newFileName = uniqid('img_', true) . '.' . $imageFileType;
                    $target_dir_portada = "../upload/sitios/portadas/";
                    $target_file_portada = $target_dir_portada . $newFileName;

                    // Intentar mover la nueva imagen de portada
                    if (move_uploaded_file($imgPortada['tmp_name'], $target_file_portada)) {
                        // Agregar el campo de la imagen de portada a la consulta SQL
                        $sql .= ", foto = :imgPortada";
                    } else {
                        // Si no se puede mover la imagen, retornar error
                        $salida = array("codigo" => 0, "mensaje" => "Error al subir la nueva imagen de portada.");
                        break;
                    }
                }

                // Finalizar la consulta con la condición del ID de la sitio
                $sql .= " WHERE id_sitio = :id_sitioEdit";

                // Preparar y ejecutar la consulta
                $ejecutar = $conexion->prepare($sql);
                $ejecutar->bindParam(':nombre', $nombreSitio, PDO::PARAM_STR);
                $ejecutar->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $ejecutar->bindParam(':direccion', $direccion, PDO::PARAM_STR);
                $ejecutar->bindParam(':enlace_reservas', $enlace_reservas, PDO::PARAM_STR);

                // Vincular :imgPortada solo si se definió una nueva imagen
                if (!empty($imgPortada['name'])) {
                    $ejecutar->bindParam(':imgPortada', $newFileName, PDO::PARAM_STR);
                }

                $ejecutar->bindParam(':id_sitioEdit', $id_sitioEdit, PDO::PARAM_INT);
                $ejecutar->execute();


                // Procesar imágenes adicionales si se cargaron nuevas
                if (!empty($imgSitio) && isset($imgSitio['name']) && count($imgSitio['name']) > 0) {
                    $filesCount = count($imgSitio['name']);
                    $target_dir_img = "../upload/sitios/images/";

                    for ($i = 0; $i < $filesCount; $i++) {
                        if ($imgSitio['error'][$i] == UPLOAD_ERR_OK) {
                            // Generar un nombre único para cada imagen adicional
                            $imgName = $imgSitio['name'][$i];
                            $imageFileType = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                            $newFileName = uniqid('img_', true) . '.' . $imageFileType;
                            $target_file_img = $target_dir_img . $newFileName;

                            // Mover el archivo a la carpeta de destino para imágenes adicionales
                            if (move_uploaded_file($imgSitio['tmp_name'][$i], $target_file_img)) {
                                // Insertar el nombre de la imagen en la tabla tb_imgSitios
                                $sql = "INSERT INTO tb_imgSitios (img, id_sitio) VALUES (:img, :id_sitio)";
                                $ejecutar = $conexion->prepare($sql);
                                $ejecutar->bindParam(':img', $newFileName, PDO::PARAM_STR);
                                $ejecutar->bindParam(':id_sitio', $id_sitioEdit, PDO::PARAM_INT);
                                $ejecutar->execute();
                            } else {
                                error_log("Error al mover la imagen adicional: " . print_r(error_get_last(), true));
                            }
                        } else {
                            error_log("Error en la subida de imagen adicional: " . $imgSitio['error'][$i]);
                        }
                    }
                }

                // Retornar éxito al finalizar la actualización
                $salida = array("codigo" => 1, "mensaje" => "sitio actualizada correctamente.");
            } else {
                // Si no se proporciona un ID de sitio válido
                $salida = array("codigo" => 0, "mensaje" => "ID de sitio no proporcionado.");
            }
            break;

        case 6:
            // Eliminar sitio por ID
            if (!empty($idsitioDelete)) {
                // Primero, obtener elsitio y sus imágenes asociadas
                $sqlsitio = "SELECT foto FROM tb_sitios WHERE id_sitio = :idsitio";
                $ejecutarsitio = $conexion->prepare($sqlsitio);
                $ejecutarsitio->bindParam(':idsitio', $idsitioDelete, PDO::PARAM_INT);
                $ejecutarsitio->execute();
                $sitio = $ejecutarsitio->fetch(PDO::FETCH_ASSOC);

                if ($sitio) {
                    // Eliminar la imagen de portada
                    $rutaPortada = "../upload/sitios/portadas/" . $sitio['foto'];
                    if (file_exists($rutaPortada)) {
                        unlink($rutaPortada);
                    }

                    // Obtener imágenes adicionales
                    $sqlImagenes = "SELECT img FROM tb_imgSitios WHERE id_sitio = :idsitio";
                    $ejecutarImagenes = $conexion->prepare($sqlImagenes);
                    $ejecutarImagenes->bindParam(':idsitio', $idsitioDelete, PDO::PARAM_INT);
                    $ejecutarImagenes->execute();
                    $imagenes = $ejecutarImagenes->fetchAll(PDO::FETCH_ASSOC);

                    // Eliminar imágenes adicionales del sistema de archivos
                    foreach ($imagenes as $img) {
                        $rutaImagen = "../upload/sitios/images/" . $img['img'];
                        if (file_exists($rutaImagen)) {
                            unlink($rutaImagen);
                        }
                    }
                    // Eliminar imágenes adicionales de la base de datos
                    $sqlDeleteImagenes = "DELETE FROM tb_imgSitios WHERE id_sitio = :idsitio";
                    $ejecutarDeleteImagenes = $conexion->prepare($sqlDeleteImagenes);
                    $ejecutarDeleteImagenes->bindParam(':idsitio', $idsitioDelete, PDO::PARAM_INT);
                    $ejecutarDeleteImagenes->execute();
                    // Eliminar la sitio de la base de datos
                    $sqlDeletesitio = "DELETE FROM tb_sitios WHERE id_sitio = :idsitio";
                    $ejecutarDeletesitio = $conexion->prepare($sqlDeletesitio);
                    $ejecutarDeletesitio->bindParam(':idsitio', $idsitioDelete, PDO::PARAM_INT);
                    $ejecutarDeletesitio->execute();



                    $salida = array("codigo" => 1, "mensaje" => "sitio eliminada correctamente.");
                } else {
                    $salida = array("codigo" => 0, "mensaje" => "sitio no encontrada.");
                }
            } else {
                $salida = array("codigo" => 0, "mensaje" => "ID de sitio no proporcionado.");
            }
            break;
    }

    return $salida;
}
