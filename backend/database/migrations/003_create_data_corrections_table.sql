-- 003_create_data_corrections_table.sql
-- Migration to create the data_corrections table

CREATE TABLE IF NOT EXISTS `data_corrections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `field_corrected` VARCHAR(255) NOT NULL,
    `old_value` TEXT NULL,
    `new_value` TEXT NULL,
    `corrected_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_request_id` (`request_id`),
    CONSTRAINT `fk_data_corrections_request_id`
        FOREIGN KEY (`request_id`)
        REFERENCES `contactos` (`id`)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;