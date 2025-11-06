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
class MysqlAdminRepository implements AdminRepositoryInterface
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

    /**
     * Creates a new administrator in the database.
     *
     * @param string $username The admin's username.
     * @param string $passwordHash The hashed password.
     * @param string $name The admin's name.
     * @return Admin|null The created Admin object on success, null on failure.
     */
    public function create(string $username, string $passwordHash, string $name): ?Admin
    {
        if ($this->db === null) {
            error_log('AdminRepository Error: Database connection is not available.');
            return null;
        }

        $sql = "INSERT INTO admins (usuario, password_hash, nombre) VALUES (:usuario, :password_hash, :nombre)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':usuario', $username, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
            $stmt->bindValue(':nombre', $name, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                return new Admin($username, $passwordHash, $name, (int)$id);
            }
        } catch (PDOException $e) {
            // Log the error, especially for unique constraint violations
            error_log('AdminRepository Error - create: ' . $e->getMessage());
        }

        return null;
    }
}