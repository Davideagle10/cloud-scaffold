<?php

namespace App\Controllers;

use App\Services\RequestService;

class RequestController
{
    private ?RequestService $requestService = null;

    /**
     * Lazily instantiates the RequestService.
     */
    private function getService(): RequestService
    {
        if ($this->requestService === null) {
            $this->requestService = new RequestService();
        }
        return $this->requestService;
    }

    /**
     * Handles the creation of a new advisory request.
     * Expects a JSON body with "nombre", "correo", and optional "telefono".
     */
    public function createRequest(): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['nombre']) || !isset($data['correo'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing required fields.']);
            return;
        }

        $nombre = trim($data['nombre']);
        $correo = trim($data['correo']);
        $telefono = isset($data['telefono']) ? trim($data['telefono']) : null;

        try {
            $newRequest = $this->getService()->createAdvisoryRequest($nombre, $correo, $telefono);
            if ($newRequest) {
                http_response_code(201); // Created
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Request submitted successfully.',
                    'request' => [
                        'id' => $newRequest->getId(),
                        'nombre' => $newRequest->getNombre(),
                        'correo' => $newRequest->getCorreo(),
                        'telefono' => $newRequest->getTelefono()
                    ]
                ]);
            } else {
                // This could happen if, for example, the email is a duplicate
                http_response_code(409); // Conflict
                echo json_encode(['error' => 'Conflict', 'message' => 'The request could not be processed. The email may already exist.']);
            }
        } catch (\Exception $e) {
            error_log('Request Creation Error: ' . $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred while processing your request.']);
        }
    }

    /**
     * Handles fetching all advisory requests for the dashboard.
     */
    public function getRequests(): void
    {
        try {
            $requests = $this->getService()->fetchAllRequests();

            // Format the data for the frontend
            $formattedRequests = array_map(function ($request) {
                return [
                    'id' => $request->getId(),
                    'nombre' => $request->getNombre(),
                    'correo' => $request->getCorreo(),
                    'telefono' => $request->getTelefono(),
                    'estado' => $request->getEstado(),
                    'fecha_solicitud' => (new \DateTime($request->getCreatedAt()))->format('Y-m-d H:i:s'),
                ];
            }, $requests);

            http_response_code(200);
            echo json_encode($formattedRequests);

        } catch (\Exception $e) {
            error_log('Fetch Requests Error: ' . $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred while fetching requests.']);
        }
    }

    /**
     * Handles fetching a single request summary.
     */
    public function getRequestSummary(): void
    {
        // This is a simplified way to get the ID from the URL,
        // assuming a URL structure like /v1/requests/summary/123
        $pathParts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
        $id = end($pathParts);

        if (!is_numeric($id)) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid request ID.']);
            return;
        }

        try {
            $summary = $this->getService()->fetchRequestSummary((int)$id);

            if ($summary) {
                http_response_code(200);
                echo json_encode($summary);
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['error' => 'Not Found', 'message' => 'Request summary not found.']);
            }
        } catch (\Exception $e) {
            error_log('Fetch Request Summary Error: ' . $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred while fetching the request summary.']);
        }
    }

   /**
     * Handles fetching a single request by its ID.
     * The ID is expected to be the last part of the URI.
     */
    public function getRequest(): void
    {
        $id = $this->getIdFromPath();
        if (!$id) return;

        try {
            $request = $this->getService()->fetchRequestById($id);

            if ($request) {
                http_response_code(200);
                echo json_encode([
                    'id' => $request->getId(),
                    'nombre' => $request->getNombre(),
                    'correo' => $request->getCorreo(),
                    'telefono' => $request->getTelefono(),
                    'idiomas' => $request->getIdiomas(), // Assuming this method exists
                    'estado_contacto' => (bool)$request->getEstado(),
                    'fecha_creacion' => (new \DateTime($request->getCreatedAt()))->format('Y-m-d H:i:s'),
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Not Found', 'message' => 'Request not found.']);
            }
        } catch (\Exception $e) {
            error_log('Fetch Request Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred while fetching the request.']);
        }
    }

    /**
     * Handles updating the contact status of a request.
     * The ID is expected to be in the URI.
     */
    public function updateStatus(): void
    {
        $id = $this->getIdFromPath(true); // Expects '/status' at the end
        if (!$id) return;

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['contactado'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing "contactado" field.']);
              return;
        }

        try {
            $success = $this->getService()->updateRequestStatus($id, (bool)$data['contactado']);
            if ($success) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Contact status updated successfully.']);
            } else {
                http_response_code(404); // Or 500 if the failure is not due to not found
                echo json_encode(['error' => 'Update Failed', 'message' => 'Could not update contact status.']);
            }
        } catch (\Exception $e) {
            error_log('Update Status Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred.']);
        }
    }

    /**
     * Extracts the numeric ID from the request URI.
     * @param bool $trimStatusPath If true, trims '/status' from the end of the path.
     * @return int|null
     */
    private function getIdFromPath(bool $trimStatusPath = false): ?int
    {
        $path = $_SERVER['PATH_INFO'] ?? '';
        if ($trimStatusPath) {
            $path = preg_replace('/\/status$/', '', $path);
        }

        $segments = explode('/', $path);
        $id = end($segments);

        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid or missing request ID.']);
            return null;
        }
        return (int)$id;
    }

    /**
     * Handles the API request for correcting a data field.
     */
    public function correctData(): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Validate input data
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['requestId'], $data['campoACorregir'], $data['valorAnterior'], $data['valorNuevo'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing required fields.']);
            $result = $this->getService()->correctData(
                (int)$data['requestId'],
                (string)$data['campoACorregir'],
                (string)$data['valorAnterior'],
                (string)$data['valorNuevo']
            );

            if ($result['success']) {
                http_response_code(200); // OK
                echo json_encode(['status' => 'success', 'message' => $result['message']]);
            } else {
                // Use the status code provided by the service
                http_response_code($result['code'] ?? 400);
                echo json_encode(['error' => 'Operation Failed', 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            error_log('Correct Data Error: ' . $e->getMessage());
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred.']);
        }
    }
}