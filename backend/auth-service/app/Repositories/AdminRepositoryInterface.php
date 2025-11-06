<?php

namespace App\Repositories;

use App\Models\Admin;

interface AdminRepositoryInterface
{
    public function findByUsername(string $username): ?Admin;
    public function findByEmailAndPassword(string $email, string $password): ?Admin;
    public function create(string $username, string $passwordHash, string $name): ?Admin;
}
