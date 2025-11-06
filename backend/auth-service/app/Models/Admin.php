<?php

namespace App\Models;

/**
 * Entity Admin
 * Represents a record from the `admins` table.
 */
class Admin
{
    public function __construct(
        private string $usuario,
        private string $password_hash,
        private ?string $nombre = null,
        private ?int $id = null
    ) {}

    // --- Getters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): string
    {
        return $this->usuario;
    }

    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }
}