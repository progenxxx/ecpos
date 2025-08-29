<?php 

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\ConnectionInterface;
use App\Models\StoreConnection;
use Exception;

class DatabaseConnectionService
{
    private array $defaultConnection = [
        'driver' => 'mysql',
        'host' => null,
        'database' => null,
        'username' => null,
        'password' => null,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ];

    public function switchDatabase(string $storeId): ConnectionInterface
    {
        try {
            $connection = $this->getConnectionConfig($storeId);
            
            config(['database.connections.dynamic' => $connection]);
            DB::purge('dynamic');
            DB::reconnect('dynamic');
            
            return DB::connection('dynamic');
        } catch (Exception $e) {
            throw new Exception("Failed to switch database for store {$storeId}: " . $e->getMessage());
        }
    }

    public function getConnectionConfig(string $storeId): array
    {
        if ($storeId === 'HQ2') {
            return $this->getMainConnection();
        }

        try {
            $storeConnection = StoreConnection::where('store_id', $storeId)
                ->where('is_active', true)
                ->first();

            if (!$storeConnection) {
                throw new Exception("No connection configuration found for store ID: {$storeId}");
            }

            return $this->buildConnectionFromModel($storeConnection);
        } catch (Exception $e) {
            throw new Exception("Failed to get connection config for {$storeId}: " . $e->getMessage());
        }
    }

    protected function getMainConnection(): array
    {
        return array_merge($this->defaultConnection, [
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE', 'ecpostarlac'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);
    }

    protected function buildConnectionFromModel(StoreConnection $connection): array
    {
        return array_merge($this->defaultConnection, [
            'host' => $connection->host ?: env('DB_HOST'),
            'database' => $connection->database_name,
            'username' => $connection->username ?: env('DB_USERNAME'),
            'password' => $connection->password ?: env('DB_PASSWORD'),
        ]);
    }

    public function setCustomConnection(array $connectionDetails): void
    {
        if ($connectionDetails['store_id'] === 'HQ2') {
            throw new Exception("Cannot modify HQ2 connection. It is managed through environment variables.");
        }

        StoreConnection::updateOrCreate(
            ['store_id' => $connectionDetails['store_id']],
            [
                'database_name' => $connectionDetails['database_name'],
                'host' => $connectionDetails['host'] ?? env('DB_HOST'),
                'username' => $connectionDetails['username'] ?? env('DB_USERNAME'),
                'password' => $connectionDetails['password'] ?? env('DB_PASSWORD'),
                'is_active' => $connectionDetails['is_active'] ?? true,
            ]
        );
    }

    public function removeCustomConnection(string $storeId): void
    {
        if ($storeId === 'HQ2') {
            throw new Exception("Cannot remove HQ2 connection. It is the main database.");
        }

        StoreConnection::where('store_id', $storeId)->delete();
    }

    public function getCustomConnections(): array
    {
        return StoreConnection::where('is_active', true)->get()->toArray();
    }
}