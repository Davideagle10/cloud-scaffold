<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\AdminRepository;

class AuthService
{
    private AdminRepository $adminRepository;

    public function __construct()
    {
        $this->adminRepository = new AdminRepository();
    }

    /**
     * Authenticates an administrator based on username and password.
     *
     * @param string $username The admin's username.
     * @param string $password The admin's plain-text password.
     * @return Admin|null The Admin object on success, null on failure.
     */
    public function authenticate(string $username, string $password): ?Admin
    {
        $admin = $this->adminRepository->findByUsername($username);

        if ($admin && password_verify($password, $admin->getPasswordHash())) {
            return $admin;
        }

        return null;
    }
}