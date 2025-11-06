<?php

namespace App\Models;

/**
 * Entity Request
 * Represents a record from the `contactos` table (an advisory request).
 */
class Request
{
    public function __construct(
        private string $nombre,
        private string $correo,
        private string $email,
        private ?string $telefono,
        private int|string|null $id = null,
        private string $estado = 'nuevo',
        private ?string $created_at = null,
        private ?array $idiomas = null
    ) {
    }

    // --- Getters ---
    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCorreo(): string
    {
        return $this->correo;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    /**
     * Returns the languages as an array.
     * @return array|null
     */
    public function getIdiomas(): ?array
    {
        return $this->idiomas;
    }
}
