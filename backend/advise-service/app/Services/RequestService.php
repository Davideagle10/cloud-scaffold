<?php

namespace App\Services;

use App\Models\Request;
use App\Repositories\RequestRepositoryInterface;
use App\Repositories\DynamoDbRequestRepository;
use App\Repositories\MysqlRequestRepository;

class RequestService
{
    private RequestRepositoryInterface $requestRepository;

    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    public static function create(): self
    {
        $dbDriver = $_ENV['DB_DRIVER'] ?? 'mysql';

        if ($dbDriver === 'dynamodb') {
            $repository = new DynamoDbRequestRepository();
        } else {
            $repository = new MysqlRequestRepository();
        }

        return new self($repository);
    }

    public function createRequest(string $nombre, string $correo, string $telefono, ?array $idiomas): ?Request
    {
        $request = new Request($nombre, $correo,$correo, $telefono, null, 'pending', date('Y-m-d H:i:s'), $idiomas);
        return $this->requestRepository->save($request);
    }

    public function getRequests(): array
    {
        return $this->requestRepository->findAll();
    }

    public function getRequest(int $id): ?Request
    {
        return $this->requestRepository->findById($id);
    }

    public function getRequestSummary(int $id): ?array
    {
        return $this->requestRepository->findSummaryById($id);
    }

    public function updateStatus(int $id, bool $contactado): bool
    {
        return $this->requestRepository->updateStatus($id, $contactado);
    }

    public function correctData(int $requestId, string $field, string $newValue): bool
    {
        return $this->requestRepository->updateField($requestId, $field, $newValue);
    }
}
