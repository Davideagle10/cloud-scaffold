<?php

namespace App\Repositories;

use App\Models\Request;

interface RequestRepositoryInterface
{
    public function save(Request $request): ?Request;
    public function findAll(): array;
    public function findById(string $id): ?Request;
    public function findSummaryById(int $id): ?array;
    public function updateStatus(int $id, bool $contactado): bool;
    public function updateField(int $requestId, string $field, string $newValue): bool;
}
