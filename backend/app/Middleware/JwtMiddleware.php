<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;

class JwtMiddleware
{
    private string $jwtSecret;

    public function __construct()
    {
        // This should be the same secret key used for encoding the token.
        // In a real application, store this securely (e.g., in an environment variable).
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-super-secret-key-for-jwt';
    }

    /**
     * Handles the token validation.
     *
     * @return bool True if the token is valid, false otherwise.
     */
    public function handle(): bool
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            $this->sendUnauthorizedResponse('Authorization header not found.');
            return false;
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            $this->sendUnauthorizedResponse('Malformed token.');
            return false;
        }

        $token = $parts[1];

        try {
            // The Key object is required for firebase/php-jwt v6.0+
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            // You can optionally add the decoded payload to the request for later use,
            // e.g., by setting a global variable or passing it to a request object.
            // For now, we just validate it.
        } catch (UnexpectedValueException $e) {
            // This catches errors like signature invalid, token expired, etc.
            $this->sendUnauthorizedResponse($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->sendUnauthorizedResponse('Invalid token.');
            return false;
        }

        return true;
    }

    /**
     * Sends a 401 Unauthorized response.
     *
     * @param string $message The error message to include in the response.
     */
    private function sendUnauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => $message
        ]);
    }
}