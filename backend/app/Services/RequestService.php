<?php

namespace App\Services;

use App\Models\DataCorrection;
use App\Models\Request;
use App\Repositories\DataCorrectionRepository;
use App\Repositories\RequestRepository;

class RequestService
{
    private RequestRepository $requestRepository;
    private DataCorrectionRepository $dataCorrectionRepository;

    public function __construct()
    {
        $this->requestRepository = new RequestRepository();
        $this->dataCorrectionRepository = new DataCorrectionRepository();
    }

    /**
     * Creates a new advisory request after performing validation.
     *
     * @param string $nombre The full name of the applicant.
     * @param string $correo The email of the applicant.
     * @param string|null $telefono The optional phone number of the applicant.
     * @return Request|null The created Request object or null on failure.
     */
    public function createAdvisoryRequest(string $nombre, string $correo, ?string $telefono): ?Request
    {
        // Basic validation
        if (empty($nombre) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            // In a real app, you'd throw a custom validation exception
            return null;
        }

        $request = new Request($nombre, $correo, $telefono);

        return $this->requestRepository->save($request);
    }

    /**
     * Fetches all advisory requests.
     *
     * @return array An array of Request objects.
     */
    public function fetchAllRequests(): array
    {
        return $this->requestRepository->findAll();
    }

    /**
     * Fetches the data for a request summary.
     *
     * @param int $id The ID of the request.
     * @return array|null The summary data or null if not found.
     */
    public function fetchRequestSummary(int $id): ?array
    {
        return $this->requestRepository->findSummaryById($id);
    }
     /**
     * Fetches a single advisory request by its ID.
     *
     * @param int $id The ID of the request.
     * @return Request|null The Request object or null if not found.
     */
    public function fetchRequestById(int $id): ?Request
    {
        return $this->requestRepository->findById($id);
    }

    /**
     * Updates the contact status of a request.
     *
     * @param int $id The ID of the request.
     * @param bool $contactado The new contact status.
     * @return bool True on success, false on failure.
     */
    public function updateRequestStatus(int $id, bool $contactado): bool
    {
        return $this->requestRepository->updateStatus($id, $contactado);
    }

     /**
     * Handles the logic for correcting a data field of a request.
     *
     * @param int    $requestId      The ID of the request to correct.
     * @param string $fieldToCorrect The name of the field to correct.
     * @param string $previousValue  The expected previous value for validation.
     * @param string $newValue       The new value to set.
     * @return array An associative array with 'success' and 'message' keys.
     */
    public function correctData(int $requestId, string $fieldToCorrect, string $previousValue, string $newValue): array
    {
        // 1. Fetch the original request
        $request = $this->requestRepository->findById($requestId);
        if (!$request) {
            return ['success' => false, 'message' => 'Request not found.', 'code' => 404];
        }

        // Map frontend field names to backend model getter methods
        $fieldMap = [
            'correo' => 'getCorreo',
            'telefono' => 'getTelefono',
            'nombre' => 'getNombre'
        ];

        $getter = $fieldMap[$fieldToCorrect] ?? null;

        if (!$getter || !method_exists($request, $getter)) {
            return ['success' => false, 'message' => "Field '$fieldToCorrect' is not correctable.", 'code' => 400];
        }
        $currentValue = $request->{$getter}();

        if ($currentValue !== $previousValue) {
            return ['success' => false, 'message' => 'The provided previous value does not match the current value.', 'code' => 409];
        }

        // 3. Create an audit record
        $dataCorrection = new DataCorrection(
            $requestId,
            $fieldToCorrect,
            $previousValue,
            $newValue
        );
        $auditSaved = $this->dataCorrectionRepository->save($dataCorrection);

        if (!$auditSaved) {
            // Log this error, as it's a critical failure
            error_log("Failed to save audit record for request ID: $requestId");
            return ['success' => false, 'message' => 'Failed to save audit trail.', 'code' => 500];
        }

        // 4. Update the request record
        $updateSuccess = $this->requestRepository->updateField($requestId, $fieldToCorrect, $newValue);

        if ($updateSuccess) {
            return ['success' => true, 'message' => 'Data corrected successfully.'];
        } else {
            // This is a critical error, might need a transaction rollback in a real app
            error_log("Failed to update field '$fieldToCorrect' for request ID: $requestId after saving audit record.");
            return ['success' => false, 'message' => 'Failed to update the request record.', 'code' => 500];
        }
    }
}