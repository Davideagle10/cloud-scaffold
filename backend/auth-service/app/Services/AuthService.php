<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\AdminRepositoryInterface;
use App\Repositories\DynamoDbAdminRepository;
use App\Repositories\MysqlAdminRepository;
use Firebase\JWT\JWT;

class AuthService
{
    private AdminRepositoryInterface $adminRepository;

    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public static function create(): self
    {
        $dbDriver = $_ENV['DB_DRIVER'] ?? 'mysql';

        if ($dbDriver === 'dynamodb') {
            $repository = new DynamoDbAdminRepository();
        } else {
            $repository = new MysqlAdminRepository();
        }

        return new self($repository);
    }

    public function authenticate(string $username, string $password): ?string
    {
        // For DynamoDB, retrieve by email (username) and password directly (PK+SK)
        if ($this->adminRepository instanceof DynamoDbAdminRepository) {
            $admin = $this->adminRepository->findByEmailAndPassword($username, $password);
            if ($admin) {
                $secretKey = $_ENV['JWT_SECRET'] ?? 'your-super-secret-key-for-jwt';
                $payload = [
                    'iat' => time(),
                    'exp' => time() + 3600,
                    'sub' => $admin->getId(),
                ];
                return JWT::encode($payload, $secretKey, 'HS256');
            }
            return null;
        }

        // Default (e.g., MySQL): fetch by username and verify hashed password
        $admin = $this->adminRepository->findByUsername($username);
        if ($admin && password_verify($password, $admin->getPasswordHash())) {
            $secretKey = $_ENV['JWT_SECRET'] ?? 'your-super-secret-key-for-jwt';
            $payload = [
                'iat' => time(),
                'exp' => time() + 3600,
                'sub' => $admin->getId(),
            ];
            return JWT::encode($payload, $secretKey, 'HS256');
        }

        return null;
    }

    public function register(string $username, string $password, string $name): ?Admin
    {
        if ($this->adminRepository instanceof DynamoDbAdminRepository) {
            // Store the password as SK directly (per requirement)
            return $this->adminRepository->create($username, $password, $name);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        return $this->adminRepository->create($username, $passwordHash, $name);
    }
}
