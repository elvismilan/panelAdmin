-- panelAdmin plantilla 0003 (josePerales 0009 + 0010): tabla de mensajes del formulario de Contacto (sitio publico).
-- Nombre, telefono (opcional), correo y mensaje; ademas se envia por correo al titular.
-- Convencion: tabla de negocio sin prefijo.
CREATE TABLE IF NOT EXISTS `contactos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(30) NULL DEFAULT NULL,
    `email` VARCHAR(180) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `ip` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `created` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_contactos_created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
