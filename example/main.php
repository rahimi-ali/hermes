<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use HermesFramework\Hermes\Core\Http\HttpKernel;
use HermesFramework\Hermes\Default\DependencyInjection\AutoWiredServiceContainer;
use HermesFramework\Hermes\Default\Http\CLIMateHttpKernelDebugger;
use HermesFramework\Hermes\Default\Http\FastRouteHttpRouter;
use HermesFramework\Hermes\Default\Http\Swoole\DiactorosSwooleRequestTransformer;
use HermesFramework\Hermes\Default\Http\Swoole\SwooleHttpServer;

// Enable one click coroutines --- you're code is now much more performant, but you should be aware of the asynchronous nature of it
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);

$isProd = str_starts_with(strtolower($_ENV['APP_ENV'] ?? 'dev'), 'prod');

$swooleHttpServer = new SwooleHttpServer(
    host: $_ENV['APP_HOST'] ?? '0.0.0.0',
    port: $_ENV['APP_PORT'] ?? 8080,
    requestTransformer: new DiactorosSwooleRequestTransformer(), // transforms swoole request objects to psr ServerRequestInterface
    workerNum: $_ENV['WORKER_COUNT'] ?? 1, // or you could just not set it, and it will automatically use your cpu count
);

// Make the service container and register some bindings
$serviceContainer = new AutoWiredServiceContainer();
(require(__DIR__ . '/bootstrap.php'))($serviceContainer);

// Make the router and register routes
$httpRouter = new FastRouteHttpRouter();
(require(__DIR__ . '/routes.php'))($httpRouter->getRegistrar());

// Debugger outputs some useful info for different steps of request handling + workers lifecycle
$kernelDebugger = null;
if (!$isProd) {
    $kernelDebugger = new CLIMateHttpKernelDebugger();
}

$kernel = new HttpKernel(
    httpServer: $swooleHttpServer,
    httpRouter: $httpRouter,
    serviceContainer: $serviceContainer,
    kernelDebugger: $kernelDebugger,
);

// We are now ready to handle requests
$kernel->start();
