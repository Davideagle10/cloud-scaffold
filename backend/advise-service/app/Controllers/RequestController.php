<?php

namespace App\Controllers;

use App\Services\RequestService;

class RequestController
{
    private ?RequestService $requestService = null;

    private function getService(): RequestService
    {
        if ($this->requestService === null) {
            $this->requestService = RequestService::create();
        }
        return $this->requestService;
    }

    public function createRequest(): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['nombre']) || !isset($data['correo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing required fields.']);
            return;
        }

        $nombre = trim($data['nombre']);
        $correo = trim($data['correo']);
        $telefono = isset($data['telefono']) ? trim($data['telefono']) : null;
        $idiomas = isset($data['idiomas']) ? $data['idiomas'] : null;
        
        // Validate that idiomas is an array if provided
        if ($idiomas !== null && !is_array($idiomas)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'idiomas must be an array.']);
            return;
        }

        try {
            $newRequest = $this->getService()->createRequest($nombre, $correo, $telefono, $idiomas);
            if ($newRequest === null) {
                http_response_code(500);
                echo json_encode(['error' => 'Internal Server Error', 'message' => 'Could not persist request.']);
                return;
            }
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Request submitted successfully.',
                'request' => [
                    'id' => $newRequest->getId(),
                    'nombre' => $newRequest->getNombre(),
                    'correo' => $newRequest->getCorreo(),
                    'telefono' => $newRequest->getTelefono(),
                    'idiomas' => $newRequest->getIdiomas()
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('Request Creation Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred while processing your request.']);
        }
    }

    public function getRequests(): void
    {
        try {
            $requests = $this->getService()->getRequests();

            $formattedRequests = array_map(function ($request) {
                return [
                    'id' => $request->getId(),
                    'nombre' => $request->getNombre(),
                    'correo' => $request->getCorreo(),
                    'telefono' => $request->getTelefono(),
                    'estado' => $request->getEstado(),
                    'fecha_solicitud' => (new \DateTime($request->getCreatedAt()))->format('Y-m-d H:i:s'),
                    'idiomas' => $request->getIdiomas() ?? []
                ];
            }, $requests);

            http_response_code(200);
            echo json_encode($formattedRequests);

        } catch (\Throwable $e) {
            error_log('Fetch Requests Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred while fetching requests.']);
        }
    }

    public function getRequestSummary(): void
    {
        $pathParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $id = end($pathParts);

        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid request ID.']);
            return;
        }

        try {
            $summary = $this->getService()->getRequestSummary((int)$id);

            if ($summary) {
                http_response_code(200);
                echo json_encode($summary);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Not Found', 'message' => 'Request summary not found.']);
            }
        } catch (\Throwable $e) {
            error_log('Fetch Request Summary Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred while fetching the request summary.']);
        }
    }

    public function getRequest(): void
    {
        $id = $this->getIdFromPath();
        if (!$id) return;

        try {
            $request = $this->getService()->getRequest($id);

            if ($request) {
                http_response_code(200);
                echo json_encode([
                    'id' => $request->getId(),
                    'nombre' => $request->getNombre(),
                    'correo' => $request->getCorreo(),
                    'telefono' => $request->getTelefono(),
                    'idiomas' => $request->getIdiomas(),
                    'estado_contacto' => (bool)$request->getEstado(),
                    'fecha_creacion' => (new \DateTime($request->getCreatedAt()))->format('Y-m-d H:i:s'),
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Not Found', 'message' => 'Request not found.']);
            }
        } catch (\Throwable $e) {
            error_log('Fetch Request Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An error occurred while fetching the request.']);
        }
    }

    public function updateStatus(): void
    {
        $id = $this->getIdFromPath(true);
        if (!$id) return;

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['contactado'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing "contactado" field.']);
              return;
        }

        try {
            $success = $this->getService()->updateStatus($id, (bool)$data['contactado']);
            if ($success) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Contact status updated successfully.']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Update Failed', 'message' => 'Could not update contact status.']);
            }
        } catch (\Throwable $e) {
            error_log('Update Status Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred.']);
        }
    }

    private function getIdFromPath(bool $trimStatusPath = false): ?int
    {
        $path = $_SERVER['REQUEST_URI'] ?? '';
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

    public function correctData(): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        try {
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['requestId'], $data['field'], $data['newValue'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing required fields.']);
                return;
            }

            $result = $this->getService()->correctData(
                (int)$data['requestId'],
                (string)$data['field'],
                (string)$data['newValue']
            );

            if ($result) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Data corrected successfully.']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Operation Failed', 'message' => 'Could not correct data.']);
            }
        } catch (\Throwable $e) {
            error_log('Correct Data Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred.']);
        }
    }
}
