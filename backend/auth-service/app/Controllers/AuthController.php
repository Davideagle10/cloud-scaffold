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
            $this->authService = AuthService::create();
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

        $passworEncrypter = $data['password'];

        $password = base64_decode($passworEncrypter);

        // Validar que la desencriptación base64 fue exitosa
        if ($password === false) {
            file_put_contents('php://stderr', '[ERROR] Login - Failed to decode base64 password' . PHP_EOL);
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid password encoding.']);
            return;
        }

        $passwordParts = explode(':', $password);
        if (count($passwordParts) !== 2 || $passwordParts[0] !== 'my-super-secret-key') {
            http_response_code(400); // Bad Request
            echo json_encode([
                    'error' => 'Bad Request', 
                    'message' => 'Invalid password key..',
                    'encrypted_password' => $passworEncrypter,
                    'decoded_password' => $password,
                    'password_parts' => $passwordParts

                ]);
            return;
        }

        try {
            // The authenticate method now returns a JWT string on success or null on failure.
            $token = $this->authService->authenticate($usuario, $passwordParts[1]);

            if ($token) {
                // On successful login, return the JWT.
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful.',
                    'token' => $token
                ]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode([
                    'error' => 'Unauthorized', 
                    'message' => 'Invalid username or password.',
                    'encrypted_password' => $passworEncrypter
                ]);
            }
        } catch (\Exception $e) {
            // Log the real error for debugging purposes
            file_put_contents('php://stderr', '[ERROR] Login Error: ' . $e->getMessage() . PHP_EOL);

            // Return a generic error message to the client
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred.']);
        }
    }

    /**
     * Handles the registration request for a new administrator.
     * Expects a JSON body with "usuario", "password", and "nombre".
     */
    public function register(): void
    {
        if ($this->authService === null) {
            $this->authService = AuthService::create();
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['usuario']) || !isset($data['password']) || !isset($data['nombre'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Bad Request', 'message' => 'Invalid JSON or missing fields.']);
            return;
        }

        $usuario = trim($data['usuario']);
        $password = $data['password'];
        $nombre = trim($data['nombre']);

        // Basic validation for empty fields
        if (empty($usuario) || empty($password) || empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Username, password, and name cannot be empty.']);
            return;
        }

        try {
            $admin = $this->authService->register($usuario, $password, $nombre);

            if ($admin) {
                http_response_code(201); // Created
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Administrator registered successfully.',
                    'admin' => [
                        'id' => $admin->getId(),
                        'name' => $admin->getNombre(),
                        'username' => $admin->getUsuario()
                    ]
                ]);
            } else {
                // This could be due to a duplicate username or other database error
                http_response_code(409); // Conflict
                echo json_encode(['error' => 'Conflict', 'message' => 'Username may already exist or another error occurred.']);
            }
        } catch (\Exception $e) {
            error_log('Registration Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred during registration.']);
        }
    }
}