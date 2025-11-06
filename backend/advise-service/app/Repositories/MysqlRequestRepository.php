<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Request;
use PDO;
use PDOException;

/**
 * RequestRepository
 * Provides methods to interact with the `contactos` table (advisory requests).
 */
class MysqlRequestRepository implements RequestRepositoryInterface
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Saves a new advisory request to the database.
     *
     * @param Request $request The Request object with data to insert.
     * @return Request|null The created Request object with its new ID, or null on failure.
     */
    public function save(Request $request): ?Request
    {
        if ($this->db === null) {
            error_log('RequestRepository Error: Database connection is not available.');
            return null;
        }

        // Include idiomas in the SQL statement
        $sql = "INSERT INTO contactos (nombre, correo, telefono, idiomas) VALUES (:nombre, :correo, :telefono, :idiomas)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $request->getNombre(), PDO::PARAM_STR);
            $stmt->bindValue(':correo', $request->getCorreo(), PDO::PARAM_STR);
            $stmt->bindValue(':telefono', $request->getTelefono(), PDO::PARAM_STR);
            
            // Convert array to JSON for storage
            $idiomasJson = $request->getIdiomas() ? json_encode($request->getIdiomas()) : null;
            $stmt->bindValue(':idiomas', $idiomasJson, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Return the newly created request object, now with an ID
                $id = $this->db->lastInsertId();
                return $this->findById($id);
            }
        } catch (PDOException $e) {
            // Log error, e.g., for duplicate email (unique key constraint)
            error_log('RequestRepository Error - save: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieves all advisory requests from the database.
     *
     * @return array An array of Request objects.
     */
    public function findAll(): array
    {
        if ($this->db === null) {
            error_log('RequestRepository Error: Database connection is not available.');
            return [];
        }

        $sql = "SELECT id, nombre, correo, telefono, estado, created_at, idiomas FROM contactos ORDER BY created_at DESC";
        $requests = [];

        try {
            $stmt = $this->db->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Convert JSON string back to array
                $idiomas = $row['idiomas'] ? json_decode($row['idiomas'], true) : null;
                
                $requests[] = new Request(
                    $row['nombre'],
                    $row['correo'],
                    $row['correo'],
                    $row['telefono'],
                    $row['id'],
                    $row['estado'],
                    $row['created_at'],
                    $idiomas
                );
            }
        } catch (PDOException $e) {
            error_log('RequestRepository Error - findAll: ' . $e->getMessage());
        }

        return $requests;
    }

    /**
     * Finds a single request by its ID.
     *
     * @param int $id The ID of the request.
     * @return Request|null
     */
    public function findById(string $id): ?Request
    {
        if ($this->db === null) {
            return null;
        }

        $sql = "SELECT * FROM contactos WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Convert JSON string back to array
                $idiomas = $row['idiomas'] ? json_decode($row['idiomas'], true) : null;
                
                 return new Request(
                    $row['nombre'],
                    $row['correo'],
                    $row['correo'],
                    $row['telefono'],
                    $row['id'],
                    $row['estado'],
                    $row['created_at'],
                    $idiomas
                );
            }
        } catch (PDOException $e) {
            error_log('RequestRepository Error - findById: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Finds the necessary data for a request summary by its ID.
     *
     * @param int $id The ID of the request.
     * @return array|null An associative array with summary data or null if not found.
     */
    public function findSummaryById(int $id): ?array
    {
        $request = $this->findById($id);
        if (!$request) {
            return null;
        }

        // Fetch associated languages.
        // Note: The table `solicitud_idiomas` and column `idioma_nombre` are assumed
        // based on the requirements, as the schema details are not provided.
        $languages = [];
        if ($this->db) {
            $sql = "SELECT idioma_nombre FROM solicitud_idiomas WHERE solicitud_id = :id";
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $languages[] = $row['idioma_nombre'];
                }
            } catch (PDOException $e) {
                error_log('RequestRepository Error - findSummaryById languages: ' . $e->getMessage());
                // Depending on requirements, you might want to return null or an empty array for languages.
            }
        }

        return [
            'nombreSolicitante' => $request->getNombre(),
            'estado' => $request->getEstado(),
            'idiomasSolicitados' => $languages,
            'requestId' => $request->getId()
        ];
    }

     /**
     * Updates the contact status of a request.
     *
     * @param int $id The ID of the request to update.
     * @param bool $contactado The new contact status.
     * @return bool True on success, false on failure.
     */
    public function updateStatus(int $id, bool $contactado): bool
    {
        if ($this->db === null) {
            return false;
        }

        // The 'estado' column seems to be the one for contact status
        $sql = "UPDATE contactos SET estado = :estado, fecha_contacto = :fecha_contacto WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':estado', $contactado, PDO::PARAM_BOOL);
            // Update fecha_contacto to the current time if being marked as contacted
            $stmt->bindValue(':fecha_contacto', $contactado ? date('Y-m-d H:i:s') : null, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('RequestRepository Error - updateStatus: ' . $e->getMessage());
           return false;
        }
    }

     /**
     * Updates a specific field for a given request.
     *
     * @param int    $requestId The ID of the request to update.
     * @param string $field     The name of the field to update (e.g., 'nombre', 'correo', 'telefono').
     * @param string $newValue  The new value for the field.
     * @return bool True on success, false on failure.
     */
    public function updateField(int $requestId, string $field, string $newValue): bool
    {
        if ($this->db === null) {
            error_log('RequestRepository Error: Database connection is not available.');
            return false;
        }

        // Whitelist of updatable fields to prevent SQL injection
        $allowedFields = ['nombre', 'correo', 'telefono'];
        if (!in_array($field, $allowedFields)) {
            error_log("RequestRepository Error - updateField: Attempt to update a non-whitelisted field: $field");
            return false;
        }

        // The column name is safe now because it's from a whitelist
        $sql = "UPDATE contactos SET $field = :newValue WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':newValue', $newValue, PDO::PARAM_STR);
            $stmt->bindValue(':id', $requestId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('RequestRepository Error - updateField: ' . $e->getMessage());
            return false;
        }
    }
}