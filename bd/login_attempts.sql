-- Tabla para rate limiting de intentos de login
-- Prefijo configurable via DB_PREFIX (default: wr_)
CREATE TABLE IF NOT EXISTS `wr_login_attempts` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `ip`           VARCHAR(45)     NOT NULL,
    `attempted_at` DATETIME        NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_ip_attempted` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
