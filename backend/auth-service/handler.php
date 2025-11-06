<?php

require __DIR__.'/api/bootstrap.php';

use App\Controllers\AuthController;

return function ($event) {
    // Extract HTTP details from event
    $method = $event['requestContext']['http']['method'] ?? $event['httpMethod'] ?? 'GET';
    $path = $event['requestContext']['http']['path'] ?? $event['path'] ?? '/';
    
    // Set up PHP superglobals for existing code
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['HTTP_AUTHORIZATION'] = $event['headers']['authorization'] ?? $event['headers']['Authorization'] ?? '';
    
    // Handle body for POST requests
    if (in_array($method, ['POST', 'PUT']) && !empty($event['body'])) {
        $parsedBody = json_decode($event['body'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $_POST = $parsedBody;
        }
    }
    
    // Route mapping
    $routes = [
        'POST' => [
            '/api/v1/auth/login' => [AuthController::class, 'login'],
            '/api/v1/auth/register' => [AuthController::class, 'register'],
        ],
    ];
    
    // Find handler
    $handler = $routes[$method][$path] ?? null;
    
    // Start output buffering
    ob_start();
    
    if ($handler) {
        try {
            $controller = new $handler[0]();
            $controller->{$handler[1]}();
            $body = ob_get_clean();
            
            return [
                'statusCode' => 200,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*',
                ],
                'body' => $body ?: json_encode(['message' => 'Success']),
            ];
        } catch (Exception $e) {
            ob_end_clean();
            return [
                'statusCode' => 500,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['error' => $e->getMessage()]),
            ];
        }
    }
    
    ob_end_clean();
    return [
        'statusCode' => 404,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode(['error' => 'Not Found']),
    ];
};

