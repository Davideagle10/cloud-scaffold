<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\DataCorrection;
use PDO;
use PDOException;

/**
 * DataCorrectionRepository
 * Provides methods to interact with the `data_corrections` table.
 */
class DataCorrectionRepository
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Saves a new data correction record to the database.
     *
     * @param DataCorrection $dataCorrection The DataCorrection object to save.
     * @return bool True on success, false on failure.
     */
    public function save(DataCorrection $dataCorrection): bool
    {
        if ($this->db === null) {
            error_log('DataCorrectionRepository Error: Database connection is not available.');
            return false;
        }

        $sql = "INSERT INTO data_corrections (request_id, field_corrected, old_value, new_value) VALUES (:request_id, :field_corrected, :old_value, :new_value)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':request_id', $dataCorrection->getRequestId(), PDO::PARAM_INT);
            $stmt->bindValue(':field_corrected', $dataCorrection->getFieldCorrected(), PDO::PARAM_STR);
            $stmt->bindValue(':old_value', $dataCorrection->getOldValue(), PDO::PARAM_STR);
            $stmt->bindValue(':new_value', $dataCorrection->getNewValue(), PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('DataCorrectionRepository Error - save: ' . $e->getMessage());
            return false;
        }
    }
}