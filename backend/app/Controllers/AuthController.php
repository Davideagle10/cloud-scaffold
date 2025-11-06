<?php

namespace App\Controllers;

// This will use AuthService, which we will create soon.
use App\Services\AuthService;

class AuthController
{
    private ?AuthService $authService = null;

    /**
     * Handles the login request for an administrator.
     * Expects a JSON body with "usuario" and "password".
     */
    public function login(): void
    {
        // Lazily instantiate the service to avoid issues if a dependency fails
        if ($this->authService === null) {
            $this->authService = new AuthService();
        }

        // Get the raw POST data from the request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Input validation
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['usuario']) || !isset($data['password'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing credentials.']);
            return;
        }

        $usuario = trim($data['usuario']);
        $password = $data['password'];

        try {
            $admin = $this->authService->authenticate($usuario, $password);

            if ($admin) {
                // In a stateless API, we would issue a token (e.g., JWT).
                // For this simulation, we'll return a success message.
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful.',
                    // Exposing limited admin data is fine for this context
                    'admin' => [
                        'id' => $admin->getId(),
                        'name' => $admin->getNombre(),
                        'username' => $admin->getUsuario()
                    ]
                ]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid username or password.']);
            }
        } catch (\Exception $e) {
            // Log the real error for debugging purposes
            error_log('Login Error: ' . $e->getMessage());

            // Return a generic error message to the client
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred.']);
        }
    }
}