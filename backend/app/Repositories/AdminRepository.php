<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Admin;
use PDO;
use PDOException;

/**
 * AdminRepository
 * Provides methods to interact with the `admins` table.
 */
class AdminRepository
{
    private ?PDO $db;

    public function __construct()
    {
        // Use the centralized, secure database connection
        $this->db = Database::getConnection();
    }

    /**
     * Finds an administrator by their username.
     *
     * @param string $username The username of the administrator.
     * @return Admin|null Returns an Admin object if found, otherwise null.
     */
    public function findByUsername(string $username): ?Admin
    {
        // Ensure the database connection is available
        if ($this->db === null) {
            error_log('AdminRepository Error: Database connection is not available.');
            return null;
        }

        $sql = "SELECT id, usuario, password_hash, nombre FROM admins WHERE usuario = :usuario LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':usuario', $username, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Create and return an Admin object with data from the DB
                return new Admin(
                    $row['usuario'],
                    $row['password_hash'],
                    $row['nombre'],
                    $row['id']
                );
            }
        } catch (PDOException $e) {
            // Log the error for debugging, don't expose it to the user
            error_log('AdminRepository Error - findByUsername: ' . $e->getMessage());
        }

        return null;
    }
}