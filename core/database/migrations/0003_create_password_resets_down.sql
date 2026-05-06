-- Migration: 0003_create_password_resets (DOWN)
-- Description: Elimina la tabla de tokens de recuperacion de contrasena
DROP TABLE IF EXISTS `password_resets`;
