-- Migration: 0003_create_password_resets
-- Description: Tabla para tokens de recuperacion de contrasena
CREATE TABLE IF NOT EXISTS `wr_password_resets` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(255)  NOT NULL,
    `token`      VARCHAR(64)   NOT NULL,
    `created_at` DATETIME      NOT NULL,
    `used`       TINYINT(1)    NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `idx_token`   (`token`),
    INDEX        `idx_email`   (`email`),
    INDEX        `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
