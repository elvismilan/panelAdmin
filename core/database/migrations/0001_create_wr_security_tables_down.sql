-- Migration down: 0001_create_wr_security_tables

DROP TABLE IF EXISTS `wr_password_resets`;
DROP TABLE IF EXISTS `wr_migrations`;
DROP TABLE IF EXISTS `wr_logs`;
DROP TABLE IF EXISTS `wr_login_attempts`;
