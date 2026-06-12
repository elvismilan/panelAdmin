-- Migration: 0001_create_wr_security_tables
-- Description: Tablas core de seguridad, auditoria tecnica y recuperacion

CREATE TABLE IF NOT EXISTS `wr_login_attempts` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip`           VARCHAR(45)  NOT NULL,
    `attempted_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_ip_attempted` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_logs` (
    `log_id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `log_accion`       TEXT NULL,
    `log_usu_id`       VARCHAR(100) NULL,
    `log_fecha`        DATE NULL,
    `log_hora`         TIME NOT NULL,
    `log_tipo_accion`  VARCHAR(300) NOT NULL,
    `log_ip`           VARCHAR(255) NOT NULL,
    `log_pc`           VARCHAR(255) NOT NULL,
    PRIMARY KEY (`log_id`),
    INDEX `idx_log_usu_fecha` (`log_usu_id`, `log_fecha`),
    INDEX `idx_log_tipo_fecha` (`log_tipo_accion`, `log_fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `run_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_migration` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_password_resets` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(64)  NOT NULL,
    `created_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_token` (`token`),
    INDEX `idx_email` (`email`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
