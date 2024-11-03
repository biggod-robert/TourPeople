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
 * @param string $direccion Dirección del restaurante.
 * @param string $telefono Teléfono del restaurante.
 * @param string $horario Horario de atención del restaurante.
 * @param array $imgRestaurante Imágenes adicionales del restaurante.
 * @param int $idRestaurante ID del restaurante a recuperar o editar.
 * @param int $idImagen ID de la imagen a eliminar.
 * @param int $id_restauranteEdit ID del restaurante a actualizar.
 * @param int $idRestauranteDelete ID del restaurante a eliminar.
 * 
 * @return array Resultado de la operación con códigos y mensajes.
 */
function restaurantes($conexion, $opcion, $id_documento, $nombreRestaurante, $descripcion, $imgPortada, $direccion, $enlace_reservas, $imgRestaurante, $idrestaurante, $idImagen, $id_restauranteEdit, $idRestauranteDelete) {
    $data = [];
    
    switch($opcion) {
        case 1: // Listar restaurantes
            $consulta = "SELECT id_restaurante, nombre FROM restaurantes WHERE id_documento = ?";
            $statement = $conexion->prepare($consulta);
            $statement->execute([$id_documento]);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 2: // Crear nuevo restaurante
            try {
                $conexion->beginTransaction();
                
                // Insertar restaurante
                $consulta = "INSERT INTO restaurantes (nombre, descripcion, ubi_restaurante, enlace_reservas_rest, id_documento) VALUES (?, ?, ?, ?, ?)";
                $statement = $conexion->prepare($consulta);
                $statement->execute([$nombreRestaurante, $descripcion, $direccion, $enlace_reservas, $id_documento]);
                
                $lastInsertId = $conexion->lastInsertId();
                
                // Procesar imagen de portada
                if ($imgPortada["error"] == 0) {
                    $nombre_archivo = uniqid() . "_" . $imgPortada["name"];
                    $ruta = "../upload/restaurantes/portadas/" . $nombre_archivo;
                    
                    if (move_uploaded_file($imgPortada["tmp_name"], $ruta)) {
                        $consulta = "UPDATE restaurantes SET imgPortada = ? WHERE id_restaurante = ?";
                        $statement = $conexion->prepare($consulta);
                        $statement->execute([$nombre_archivo, $lastInsertId]);
                    }
                }
                
                // Procesar imágenes adicionales
                if (!empty($imgRestaurante)) {
                    $consulta = "INSERT INTO imagenes_restaurantes (id_restaurante, img) VALUES (?, ?)";
                    $statement = $conexion->prepare($consulta);
                    
                    foreach($imgRestaurante['tmp_name'] as $key => $tmp_name) {
                        if ($imgRestaurante['error'][$key] == 0) {
                            $nombre_archivo = uniqid() . "_" . $imgRestaurante['name'][$key];
                            $ruta = "../upload/restaurantes/images/" . $nombre_archivo;
                            
                            if (move_uploaded_file($tmp_name, $ruta)) {
                                $statement->execute([$lastInsertId, $nombre_archivo]);
                            }
                        }
                    }
                }
                
                $conexion->commit();
                $data = ["codigo" => 1, "mensaje" => "Restaurante creado exitosamente"];
            } catch(Exception $e) {
                $conexion->rollBack();
                $data = ["codigo" => 0, "mensaje" => "Error al crear el restaurante: " . $e->getMessage()];
            }
            break;
            
        case 3: // Obtener información de un restaurante
            try {
                // Obtener datos del restaurante
                $consulta = "SELECT * FROM restaurantes WHERE id_restaurante = ? AND id_documento = ?";
                $statement = $conexion->prepare($consulta);
                $statement->execute([$idrestaurante, $id_documento]);
                $restaurante = $statement->fetch(PDO::FETCH_ASSOC);
                
                if ($restaurante) {
                    // Obtener imágenes del restaurante
                    $consulta = "SELECT id_img, img FROM imagenes_restaurantes WHERE id_restaurante = ?";
                    $statement = $conexion->prepare($consulta);
                    $statement->execute([$idrestaurante]);
                    $imagenes = $statement->fetchAll(PDO::FETCH_ASSOC);
                    
                    $restaurante['imagenes'] = $imagenes;
                    $data = ["codigo" => 1, "data" => $restaurante];
                } else {
                    $data = ["codigo" => 0, "mensaje" => "Restaurante no encontrado"];
                }
            } catch(Exception $e) {
                $data = ["codigo" => 0, "mensaje" => "Error al obtener información: " . $e->getMessage()];
            }
            break;
            
        case 4: // Eliminar imagen
            try {
                // Verificar propiedad de la imagen
                $consulta = "SELECT i.img, r.id_documento 
                            FROM imagenes_restaurantes i 
                            JOIN restaurantes r ON i.id_restaurante = r.id_restaurante 
                            WHERE i.id_img = ?";
                $statement = $conexion->prepare($consulta);
                $statement->execute([$idImagen]);
                $imagen = $statement->fetch(PDO::FETCH_ASSOC);
                
                if ($imagen && $imagen['id_documento'] == $id_documento) {
                    // Eliminar archivo físico
                    $ruta = "../upload/restaurantes/images/" . $imagen['img'];
                    if (file_exists($ruta)) {
                        unlink($ruta);
                    }
                    
                    // Eliminar registro de la base de datos
                    $consulta = "DELETE FROM imagenes_restaurantes WHERE id_img = ?";
                    $statement = $conexion->prepare($consulta);
                    $statement->execute([$idImagen]);
                    
                    $data = ["codigo" => 1, "mensaje" => "Imagen eliminada correctamente"];
                } else {
                    $data = ["codigo" => 0, "mensaje" => "No se encontró la imagen o no tiene permisos"];
                }
            } catch(Exception $e) {
                $data = ["codigo" => 0, "mensaje" => "Error al eliminar la imagen: " . $e->getMessage()];
            }
            break;
            
        case 5: // Actualizar restaurante
            try {
                $conexion->beginTransaction();
                
                // Actualizar información básica
                $consulta = "UPDATE restaurantes SET 
                            nombre = ?, 
                            descripcion = ?, 
                            ubi_restaurante = ?, 
                            enlace_reservas_rest = ? 
                            WHERE id_restaurante = ? AND id_documento = ?";
                $statement = $conexion->prepare($consulta);
                $statement->execute([
                    $nombreRestaurante, 
                    $descripcion, 
                    $direccion, 
                    $enlace_reservas, 
                    $id_restauranteEdit, 
                    $id_documento
                ]);
                
                // Actualizar imagen de portada si se proporcionó una nueva
                if ($imgPortada["error"] == 0) {
                    // Obtener nombre de la imagen anterior
                    $consulta = "SELECT imgPortada FROM restaurantes WHERE id_restaurante = ?";
                    $statement = $conexion->prepare($consulta);
                    $statement->execute([$id_restauranteEdit]);
                    $img_anterior = $statement->fetch(PDO::FETCH_ASSOC)['imgPortada'];
                    
                    // Eliminar imagen anterior
                    if ($img_anterior) {
                        $ruta_anterior = "../upload/restaurantes/portadas/" . $img_anterior;
                        if (file_exists($ruta_anterior)) {
                            unlink($ruta_anterior);
                        }
                    }
                    
                    // Guardar nueva imagen
                    $nombre_archivo = uniqid() . "_" . $imgPortada["name"];
                    $ruta = "../upload/restaurantes/portadas/" . $nombre_archivo;
                    
                    if (move_uploaded_file($imgPortada["tmp_name"], $ruta)) {
                        $consulta = "UPDATE restaurantes SET imgPortada = ? WHERE id_restaurante = ?";
                        $statement = $conexion->prepare($consulta);
                        $statement->execute([$nombre_archivo, $id_restauranteEdit]);
                    }
                }
                
                // Procesar nuevas imágenes si se proporcionaron
                if (!empty($imgRestaurante['name'][0])) {
                    $consulta = "INSERT INTO imagenes_restaurantes (id_restaurante, img) VALUES (?, ?)";
                    $statement = $conexion->prepare($consulta);
                    
                    foreach($imgRestaurante['tmp_name'] as $key => $tmp_name) {
                        if ($imgRestaurante['error'][$key] == 0) {
                            $nombre_archivo = uniqid() . "_" . $imgRestaurante['name'][$key];
                            $ruta = "../upload/restaurantes/images/" . $nombre_archivo;
                            
                            if (move_uploaded_file($tmp_name, $ruta)) {
                                $statement->execute([$id_restauranteEdit, $nombre_archivo]);
                            }
                        }
                    }
                }
                
                $conexion->commit();
                $data = ["codigo" => 1, "mensaje" => "Restaurante actualizado exitosamente"];
            } catch(Exception $e) {
                $conexion->rollBack();
                $data = ["codigo" => 0, "mensaje" => "Error al actualizar el restaurante: " . $e->getMessage()];
            }
            break;
            
        case 6: // Eliminar restaurante
            try {
                $conexion->beginTransaction();
                
                // Verificar propiedad del restaurante
                $consulta = "SELECT * FROM restaurantes WHERE id_restaurante = ? AND id_documento = ?";
                $statement = $conexion->prepare($consulta);
                $statement->execute([$idRestauranteDelete, $id_documento]);
                $restaurante = $statement->fetch(PDO::FETCH_ASSOC);
                
                if ($restaurante) {
                    // Eliminar imagen de portada
                    if ($restaurante['imgPortada']) {
                        $ruta = "../upload/restaurantes/portadas/" . $restaurante['imgPortada'];
                        if (file_exists($ruta)) {
                            unlink($ruta);
                        }
                    }
                    
                    // Obtener y eliminar imágenes adicionales
                    $consulta = "SELECT img FROM imagenes_restaurantes WHERE id_restaurante = ?";
                    $statement = $conexion->prepare($consulta);
                    $statement->execute([$idRestauranteDelete]);
                    $imagenes = $statement->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach($imagenes as $imagen) {
                        $ruta = "../upload/restaurantes/images/" . $imagen['img'];
                        if (file_exists($ruta)) {
                            unlink($ruta);
                        }
                    }
                    
                    // Eliminar registros de la base de datos
                    $consulta = "DELETE FROM imagenes_restaurantes WHERE id_restaurante = ?";
                    $statement = $conexion->prepare($consulta);
                    $statement->execute([$idRestauranteDelete]);
                    
                    $consulta = "DELETE FROM restaurantes WHERE id_restaurante = ?";
                    $statement = $conexion->prepare($consulta);
                    $statement->execute([$idRestauranteDelete]);
                    
                    $conexion->commit();
                    $data = ["codigo" => 1, "mensaje" => "Restaurante eliminado correctamente"];
                } else {
                    $data = ["codigo" => 0, "mensaje" => "No se encontró el restaurante o no tiene permisos"];
                }
            } catch(Exception $e) {
                $conexion->rollBack();
                $data = ["codigo" => 0, "mensaje" => "Error al eliminar el restaurante: " . $e->getMessage()];
            }
            break;
    }
    
    return $data;
}