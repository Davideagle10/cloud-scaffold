<?php

namespace App\Repositories;

use App\Models\Admin;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;

class DynamoDbAdminRepository implements AdminRepositoryInterface
{
    private DynamoDbClient $dynamoDb;
    private string $tableName;

    public function __construct()
    {
        $this->dynamoDb = new DynamoDbClient([
            'region'  => $_ENV['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_DYNAMO_USER'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY_DYNAMO_USER'],
            ],
        ]);
        $this->tableName = 'Language-Advisory-Platform-admins';
    }

    public function findByUsername(string $username): ?Admin
    {
        // Not supported with the current key schema (email + password)
        return null;
    }

    public function findByEmailAndPassword(string $email, string $password): ?Admin
    {
        try {
            $result = $this->dynamoDb->getItem([
                'TableName' => $this->tableName,
                'Key' => [
                    'email'    => ['S' => $email],
                    'password' => ['S' => $password],
                ],
            ]);

            if (isset($result['Item'])) {
                $item = $result['Item'];
                return new Admin(
                    $item['email']['S'],
                    $item['password']['S'],
                    $item['name']['S'] ?? null,
                    null
                );
            }
        } catch (AwsException $e) {
            error_log('DynamoDbAdminRepository Error - findByEmailAndPassword: ' . $e->getMessage());
        }

        return null;
    }

    public function create(string $username, string $passwordHash, string $name): ?Admin
    {
        // For DynamoDB we store email as PK and password as SK (plain, per requirement)
        try {
            $this->dynamoDb->putItem([
                'TableName' => $this->tableName,
                'Item' => [
                    'email'    => ['S' => $username],
                    'password' => ['S' => $passwordHash],
                    'name'     => ['S' => $name],
                ],
                'ConditionExpression' => 'attribute_not_exists(email) AND attribute_not_exists(password)'
            ]);

            return new Admin($username, $passwordHash, $name, null);
        } catch (AwsException $e) {
            error_log('DynamoDbAdminRepository Error - create: ' . $e->getMessage());
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
