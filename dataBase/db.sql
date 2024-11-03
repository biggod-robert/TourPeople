-- Deshabilitar la revisión de llaves foráneas
SET foreign_key_checks = 0;

-- Borrar todas las tablas si existen
DROP TABLE IF EXISTS `tb_like_hoteles`;
DROP TABLE IF EXISTS `tb_like_sitios`;

DROP TABLE IF EXISTS `tb_imgHoteles`;

DROP TABLE IF EXISTS `tb_hoteles`;

DROP TABLE IF EXISTS `tb_imgSitios`;

DROP TABLE IF EXISTS `tb_sitios`;

DROP TABLE IF EXISTS `tb_code_reset_pass`;

DROP TABLE IF EXISTS `tb_users`;

DROP TABLE IF EXISTS `tb_roles`;

-- Tabla de roles
CREATE TABLE `tb_roles` (
    `id_rol` TINYINT(11) NOT NULL AUTO_INCREMENT,
    `rol` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id_rol`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insertar roles predefinidos
INSERT INTO `tb_roles` (`rol`) VALUES ('administrador'), ('usuario');

-- Tabla de usuarios
CREATE TABLE `tb_users` (
    `id_documento` INT(15) NOT NULL,
    `id_rol` TINYINT(11) NOT NULL,
    `nombre_p` VARCHAR(50) NOT NULL,
    `apellido_p` VARCHAR(50) NOT NULL,
    `correo` VARCHAR(50) NOT NULL,
    `clave` VARCHAR(255) NOT NULL,
    `edad` VARCHAR(50) NOT NULL,
    `f_nacimiento` DATE NOT NULL,
    `telefono` VARCHAR(67) NOT NULL,
    `imagen` VARCHAR(3000) NOT NULL,
    `fecha_ingreso` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id_documento`),
    KEY `fk_rol_user` (`id_rol`),
    CONSTRAINT `fk_rol_user` FOREIGN KEY (`id_rol`) REFERENCES `tb_roles` (`id_rol`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Insertar un usuario
INSERT INTO
    `tb_users` (
        `id_documento`,
        `id_rol`,
        `nombre_p`,
        `apellido_p`,
        `correo`,
        `clave`,
        `edad`,
        `f_nacimiento`,
        `telefono`,
        `imagen`,
        `fecha_ingreso`
    )
VALUES (
        '1120964003',
        '1',
        'usuario',
        'admin',
        'robertmoor2003@gmail.com',
        '$2y$10$2j2W7jWgkvlGzNhVLa/q5.Llq4MFZMN1iQfaHn/hGuBW7XijMy7qG',
        '22',
        '2024-10-26',
        '3196565656',
        'user-1.jpg',
        current_timestamp()
    );

-- Tabla para código de restablecimiento de contraseña
CREATE TABLE `tb_code_reset_pass` (
    `id_code` INT NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(7) NOT NULL,
    `id_documento` INT NOT NULL,
    PRIMARY KEY (`id_code`),
    CONSTRAINT `fk_code_reset_pass_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Tabla de sitios
CREATE TABLE `tb_sitios` (
    `id_sitio` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(200) NOT NULL,
    `descripcion_sitio` varchar(10000) NOT NULL,
    `ubi_sitio` varchar(200) NOT NULL,
    `enlace_reservas_turs` varchar(300) NOT NULL,
    `foto` VARCHAR(200) NOT NULL,
    `id_documento` int(15) NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_sitio`),
    CONSTRAINT `fk_sitios_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `tb_imgSitios` (
    `id_img` INT AUTO_INCREMENT,
    `img` VARCHAR(200) NOT NULL,
    `id_sitio` INT NOT NULL,
    PRIMARY KEY (`id_img`),
    CONSTRAINT `fk_img_sitios` FOREIGN KEY (`id_sitio`) REFERENCES `tb_sitios` (`id_sitio`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Tabla hoteles
CREATE TABLE `tb_hoteles` (
    `id_hotel` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(80) NOT NULL,
    `descripcion_hotel` VARCHAR(10000) NOT NULL,
    `ubicacion_hotel` varchar(300) NOT NULL,
    `enlace_reservas` varchar(300) NOT NULL,
    `foto` VARCHAR(200) NOT NULL,
    `id_documento` INT NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_hotel`),
    CONSTRAINT `fk_hoteles_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `tb_imgHoteles` (
    `id_img` INT AUTO_INCREMENT,
    `img` VARCHAR(200) NOT NULL,
    `id_hotel` INT NOT NULL,
    PRIMARY KEY (`id_img`),
    CONSTRAINT `fk_img_Hoteles` FOREIGN KEY (`id_hotel`) REFERENCES `tb_hoteles` (`id_hotel`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- tabla restaurantes
CREATE TABLE `tb_restaurantes` (
    `id_restaurante` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(200) NOT NULL,
    `descripcion_restaurante` varchar(10000) NOT NULL,
    `ubi_restaurante` varchar(200) NOT NULL,
    `enlace_reservas_rest` varchar(300) NOT NULL,
    `foto` VARCHAR(200) NOT NULL,
    `id_documento` int(15) NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_restaurante`),
    CONSTRAINT `fk_restaurantes_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `tb_imgRestaurantes` (
    `id_img` INT AUTO_INCREMENT,
    `img` VARCHAR(200) NOT NULL,
    `id_restaurante` INT NOT NULL,
    PRIMARY KEY (`id_img`),
    CONSTRAINT `fk_img_restaurantes` FOREIGN KEY (`id_restaurante`) REFERENCES `tb_restaurantes` (`id_restaurante`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Tabla de Likes para Sitios
CREATE TABLE `tb_like_sitios` (
    `id_like` INT AUTO_INCREMENT,
    `id_documento` INT(15) NOT NULL,
    `id_sitio` INT NOT NULL,
    PRIMARY KEY (`id_like`),
    CONSTRAINT `fk_like_sitio_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`) ON DELETE CASCADE,
    CONSTRAINT `fk_like_sitio_sitio` FOREIGN KEY (`id_sitio`) REFERENCES `tb_sitios` (`id_sitio`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
-- Tabla de Likes para Hoteles
CREATE TABLE `tb_like_hoteles` (
    `id_like` INT AUTO_INCREMENT,
    `id_documento` INT(15) NOT NULL,
    `id_hotel` INT NOT NULL,
    PRIMARY KEY (`id_like`),
    CONSTRAINT `fk_like_hotel_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`) ON DELETE CASCADE,
    CONSTRAINT `fk_like_hotel_hotel` FOREIGN KEY (`id_hotel`) REFERENCES `tb_hoteles` (`id_hotel`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Tabla de Likes para restaurantes
CREATE TABLE `tb_like_restaurantes` (
    `id_like` INT AUTO_INCREMENT,
    `id_documento` INT(15) NOT NULL,
    `id_restaurante` INT NOT NULL,
    PRIMARY KEY (`id_like`),
    CONSTRAINT `fk_like_rest_user` FOREIGN KEY (`id_documento`) REFERENCES `tb_users` (`id_documento`) ON DELETE CASCADE,
    CONSTRAINT `fk_like_rest_restaurante` FOREIGN KEY (`id_restaurante`) REFERENCES `tb_restaurantes` (`id_restaurante`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- Eliminar la función buscarUsuario si existe
DROP FUNCTION IF EXISTS `buscarUsuario`;

-- Crear la función buscarUsuario
CREATE FUNCTION `buscarUsuario` (UsuarioBuscar VARCHAR(100))
RETURNS INT
BEGIN
    DECLARE resultado INT;
    SET resultado = (SELECT COUNT(*) FROM tb_users WHERE correo = UsuarioBuscar);
    IF resultado > 0 THEN
        RETURN 1;
    ELSE
        RETURN 2;
    END IF;
END;

-- Eliminar la función buscarClave si existe
DROP FUNCTION IF EXISTS `buscarClave`;

-- Crear la función buscarClave
CREATE FUNCTION `buscarClave` (UsuarioBuscar varchar(100))
RETURNS VARCHAR(500)
BEGIN
    DECLARE cla VARCHAR(500);
    SET cla = '';
    SET cla = (SELECT clave FROM tb_users WHERE correo = UsuarioBuscar);
    RETURN cla;
END;

-- Crear el trigger para eliminar el código de verificación al actualizar la contraseña
DROP TRIGGER IF EXISTS `trg_eliminar_codigo_verificacion`;

CREATE TRIGGER `trg_eliminar_codigo_verificacion`
AFTER UPDATE ON `tb_users`
FOR EACH ROW
BEGIN
    -- Verificar si la clave ha cambiado
    IF OLD.clave != NEW.clave THEN
        DELETE FROM `tb_code_reset_pass` WHERE `id_documento` = NEW.id_documento;
    END IF;
END;

-- Habilitar la revisión de llaves foráneas
SET foreign_key_checks = 1;