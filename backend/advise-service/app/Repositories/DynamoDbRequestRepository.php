<?php

namespace App\Repositories;

use App\Models\Request;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;

class DynamoDbRequestRepository implements RequestRepositoryInterface
{
    private DynamoDbClient $dynamoDb;
    private string $tableName;

    public function __construct()
    {
        $region = $_ENV['AWS_REGION'] ?? getenv('AWS_REGION') ?? 'us-east-1';
        $this->dynamoDb = new DynamoDbClient([
            'region'  => $region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_DYNAMO_USER'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_DYNAMO_USER'],
            ],
        ]);
        $this->tableName = $_ENV['DYNAMODB_TABLE_REQUESTS'] ?? getenv('DYNAMODB_TABLE_REQUESTS') ?? 'Language-Advisory-Platform-requests';
    }

    public function save(Request $request): ?Request
    {
        $id = $this->generateUuidV4();
        try {
            $timestamp = time();
            $createdAtStr = date('Y-m-d H:i:s');
            $this->dynamoDb->putItem([
                'TableName' => $this->tableName,
                'Item' => [
                    // Table keys
                    'email'      => ['S' => $request->getEmail()],
                    'created_at' => ['N' => (string)$timestamp],

                    // Non-key attributes (retain existing ones for compatibility)
                    'id'        => ['S' => $id],
                    'createdAt' => ['S' => $createdAtStr],
                    'nombre'    => ['S' => $request->getNombre()],
                    'correo'    => ['S' => $request->getCorreo()],
                    'telefono'  => $request->getTelefono() !== null ? ['S' => $request->getTelefono()] : ['NULL' => true],
                    'estado'    => ['S' => 'pending'],
                    'idiomas'   => ['S' => $request->getIdiomas() ? json_encode($request->getIdiomas()) : ''],
                ],
            ]);

            // Return constructed entity to avoid read-after-write nulls
            return new Request(
                $request->getNombre(),
                $request->getCorreo(),
                $request->getEmail(),
                $request->getTelefono(),
                $id,
                'pending',
                $createdAtStr,
                $request->getIdiomas()
            );
        } catch (AwsException $e) {
            error_log('DynamoDbRequestRepository Error - save: ' . $e->getMessage());
        }

        return null;
    }

    public function findAll(): array
    {
        $requests = [];
        try {
            $result = $this->dynamoDb->scan([
                'TableName' => $this->tableName,
                'ConsistentRead' => true,
            ]);

            foreach ($result['Items'] as $item) {
                $idiomas = !empty($item['idiomas']['S']) ? json_decode($item['idiomas']['S'], true) : null;
                $email = isset($item['email']['S']) ? $item['email']['S'] : ($item['correo']['S'] ?? '');
                $telefono = isset($item['telefono']['S']) ? $item['telefono']['S'] : null;
                $createdAt = $this->extractCreatedAtFromItem($item);

                $requests[] = new Request(
                    $item['nombre']['S'] ?? '',
                    $item['correo']['S'] ?? $email,
                    $email,
                    $telefono,
                    $item['id']['S'] ?? null,
                    $item['estado']['S'] ?? 'pending',
                    $createdAt,
                    $idiomas
                );
            }
        } catch (AwsException $e) {
            error_log('DynamoDbRequestRepository Error - findAll: ' . $e->getMessage());
        }

        return $requests;
    }

    public function findById(string $id): ?Request
    {
        try {
            // No index on id; scan and filter
            $result = $this->dynamoDb->scan([
                'TableName' => $this->tableName,
                'FilterExpression' => '#id = :id',
                'ExpressionAttributeNames' => [
                    '#id' => 'id',
                ],
                'ExpressionAttributeValues' => [
                    ':id' => ['S' => $id],
                ],
                'Limit' => 1,
                'ConsistentRead' => true,
            ]);

            if (!empty($result['Items'])) {
                $item = $result['Items'][0];
                $idiomas = !empty($item['idiomas']['S']) ? json_decode($item['idiomas']['S'], true) : null;
                $email = isset($item['email']['S']) ? $item['email']['S'] : ($item['correo']['S'] ?? '');
                $telefono = isset($item['telefono']['S']) ? $item['telefono']['S'] : null;
                $createdAt = $this->extractCreatedAtFromItem($item);

                return new Request(
                    $item['nombre']['S'] ?? '',
                    $item['correo']['S'] ?? $email,
                    $email,
                    $telefono,
                    $item['id']['S'] ?? null,
                    $item['estado']['S'] ?? 'pending',
                    $createdAt,
                    $idiomas
                );
            }
        } catch (AwsException $e) {
            error_log('DynamoDbRequestRepository Error - findById: ' . $e->getMessage());
        }

        return null;
    }

    public function findSummaryById(int $id): ?array
    {
        $request = $this->findById($id);
        if (!$request) {
            return null;
        }

        return [
            'nombreSolicitante' => $request->getNombre(),
            'estado' => $request->getEstado(),
            'idiomasSolicitados' => $request->getIdiomas() ?? [],
            'requestId' => $request->getId()
        ];
    }

    public function updateStatus(int $id, bool $contactado): bool
    {
        try {
            $keys = $this->resolveKeysById((string)$id);
            if ($keys === null) {
                return false;
            }
            $this->dynamoDb->updateItem([
                'TableName' => $this->tableName,
                'Key' => $keys,
                'UpdateExpression' => 'set estado = :s',
                'ExpressionAttributeValues' => [
                    ':s' => ['S' => $contactado ? 'contacted' : 'pending'],
                ],
            ]);

            return true;
        } catch (AwsException $e) {
            error_log('DynamoDbRequestRepository Error - updateStatus: ' . $e->getMessage());
        }

        return false;
    }

    public function updateField(int $requestId, string $field, string $newValue): bool
    {
        $allowedFields = ['nombre', 'correo', 'telefono'];
        if (!in_array($field, $allowedFields)) {
            error_log("DynamoDbRequestRepository Error - updateField: Attempt to update a non-whitelisted field: $field");
            return false;
        }

        try {
            $keys = $this->resolveKeysById((string)$requestId);
            if ($keys === null) {
                return false;
            }
            $this->dynamoDb->updateItem([
                'TableName' => $this->tableName,
                'Key' => $keys,
                'UpdateExpression' => "set $field = :v",
                'ExpressionAttributeValues' => [
                    ':v' => ['S' => $newValue],
                ],
            ]);

            return true;
        } catch (AwsException $e) {
            error_log('DynamoDbRequestRepository Error - updateField: ' . $e->getMessage());
        }

        return false;
    }

    private function resolveKeysById(string $id): ?array
    {
        try {
            $result = $this->dynamoDb->scan([
                'TableName' => $this->tableName,
                'FilterExpression' => '#id = :id',
                'ExpressionAttributeNames' => [
                    '#id' => 'id',
                ],
                'ExpressionAttributeValues' => [
                    ':id' => ['S' => $id],
                ],
                'Limit' => 1,
                'ConsistentRead' => true,
            ]);
            if (!empty($result['Items'])) {
                $item = $result['Items'][0];
                $createdAtKey = null;
                if (isset($item['created_at']['N'])) {
                    $createdAtKey = ['N' => $item['created_at']['N']];
                } elseif (isset($item['created_at']['S'])) {
                    $createdAtKey = ['S' => $item['created_at']['S']];
                } elseif (isset($item['createdAt']['S'])) {
                    $createdAtKey = ['N' => (string)strtotime($item['createdAt']['S'])];
                }
                if (!isset($item['email']['S']) || $createdAtKey === null) {
                    return null;
                }
                return [
                    'email' => ['S' => $item['email']['S']],
                    'created_at' => $createdAtKey,
                ];
            }
        } catch (AwsException $e) {
            error_log('DynamoDbRequestRepository Error - resolveKeysById: ' . $e->getMessage());
        }
        return null;
    }

    private function extractCreatedAtFromItem(array $item): ?string
    {
        if (isset($item['createdAt']['S']) && is_string($item['createdAt']['S'])) {
            return $item['createdAt']['S'];
        }
        if (isset($item['created_at']['N']) && is_string($item['created_at']['N'])) {
            return date('Y-m-d H:i:s', (int)$item['created_at']['N']);
        }
        if (isset($item['created_at']['S']) && is_string($item['created_at']['S'])) {
            $val = $item['created_at']['S'];
            if (ctype_digit($val)) {
                return date('Y-m-d H:i:s', (int)$val);
            }
            return $val;
        }
        return null;
    }

    private function generateUuidV4(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
