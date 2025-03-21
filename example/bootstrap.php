<?php

declare(strict_types=1);

use HermesFramework\Hermes\Core\DependencyInjection\ConfigurableServiceContainer;
use HermesFramework\Hermes\Core\DependencyInjection\ServiceContainer;
use HermesFramework\Hermes\Core\Http\ExceptionHandler;
use HermesFramework\Hermes\Default\Http\WhoopsExceptionHandler;

use function Swoole\Coroutine\defer;

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOProxy;

return function (ConfigurableServiceContainer $serviceContainer): void {
    // Register Exception Handler -- Could have fetched other services from the service container if they were needed
    $serviceContainer->bindOnce(ExceptionHandler::class, fn () => new WhoopsExceptionHandler());

    $connectionPool = new PDOPool(
        new PDOConfig()
            ->withHost($_ENV['DB_HOST'] ?? '127.0.0.1')
            ->withPort($_ENV['DB_PORT'] ?? 3306)
            ->withDbName($_ENV['DB_NAME'] ?? 'hermes')
            ->withCharset($_ENV['DB_CHARSET'] ?? 'utf8mb4')
            ->withUsername($_ENV['DB_USER'] ?? 'root')
            ->withPassword($_ENV['DB_PASSWORD'] ?? 'root'),
        // Quick Note: this size is per each worker so if you have 12 workers this would result in at most 96 open connections
        (int)($_ENV['DB_POOL_SIZE'] ?? 8),
    );

    $serviceContainer->bindOnce('db_pool', $connectionPool);

    $serviceContainer->bindOncePerScope(PDOProxy::class, function (ServiceContainer $serviceContainer) {
        $pool = $serviceContainer->get('db_pool');
        $pdoConnection = $pool->get();
        echo "got a connection out of the pool\n";

        defer(function () use ($pool, $pdoConnection): void {
            if ($pdoConnection->inTransaction()) {
                trigger_error('Transaction was not closed in coroutine', E_USER_WARNING);
                $pdoConnection->rollback();
            }
            $pool->put($pdoConnection);
            echo "released the connection to the pool\n";
        });

        return $pdoConnection;
    });
    $serviceContainer->alias('pdo', PDOProxy::class);
};
