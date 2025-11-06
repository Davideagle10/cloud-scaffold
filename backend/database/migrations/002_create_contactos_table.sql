-- 002_create_contactos_table.sql
-- Migration to create the contactos table for advisory requests

CREATE TABLE IF NOT EXISTS `contactos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `correo` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(50) NULL,
    `estado` VARCHAR(50) NOT NULL DEFAULT 'nuevo',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_contacto` DATETIME NULL,
    `idiomas` TEXT NULL,
    UNIQUE KEY `idx_unique_correo` (`correo`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;