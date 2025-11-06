-- 004_create_solicitud_idiomas_table.sql
-- Migration to create the solicitud_idiomas pivot table

CREATE TABLE IF NOT EXISTS `solicitud_idiomas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT NOT NULL,
    `idioma_nombre` VARCHAR(255) NOT NULL,
    CONSTRAINT `fk_solicitud_idiomas_solicitud_id`
        FOREIGN KEY (`solicitud_id`)
        REFERENCES `contactos` (`id`)
        ON DELETE RESTRICT,
    UNIQUE KEY `idx_unique_solicitud_idioma` (`solicitud_id`, `idioma_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;