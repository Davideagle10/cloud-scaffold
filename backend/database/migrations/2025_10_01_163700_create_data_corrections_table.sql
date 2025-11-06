CREATE TABLE data_corrections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    field_corrected VARCHAR(255) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    corrected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- corrected_by INT, -- Optional: If you have user authentication
    FOREIGN KEY (request_id) REFERENCES contactos(id) ON DELETE CASCADE
);