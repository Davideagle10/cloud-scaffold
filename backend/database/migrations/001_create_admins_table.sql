-- 001_create_admins_table.sql
-- Migration to create the admins table

CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `nombre` VARCHAR(255) NULL,
    UNIQUE KEY `idx_unique_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;