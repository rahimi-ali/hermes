<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Default\Http\Swoole;

use Psr\Http\Server\RequestHandlerInterface;
use RahimiAli\Hermes\Core\Http\HttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class SwooleHttpServer implements HttpServer
{
    private const array BODY_PROHIBITED_REQUEST_METHODS = ['HEAD', 'OPTIONS']; // OPTIONS is not a must but does reduce unnecessary overhead

    private readonly Server $server;

    private readonly SwooleResponseEmitter $emitter;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly SwooleRequestTransformer $requestTransformer,
        private readonly int|null $workerNum = null,
        private readonly int $maxUploadSize = 10 * 1024 * 1024,
        private readonly int $maxWorkerConcurrency = 4096,
    ) {
        $this->server = new Server($this->host, $this->port, SWOOLE_BASE);
        $this->server->set([
            'worker_num' => $this->workerNum ?? swoole_cpu_num(),
            'upload_max_filesize' => $this->maxUploadSize,
            'worker_max_concurrency' => $this->maxWorkerConcurrency,
            'http_parse_post' => false,
        ]);
        $this->emitter = new SwooleResponseEmitter();
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function onWorkerStart(callable $callback): static
    {
        $this->server->on('WorkerStart', $callback);
        return $this;
    }

    public function onWorkerShutdown(callable $callback): static
    {
        $this->server->on('WorkerStop', $callback);
        return $this;
    }

    public function onRequest(RequestHandlerInterface $handler): static
    {
        $this->server->on('request', function (Request $request, Response $response) use ($handler): void {
            $psrRequest = $this->requestTransformer->transform($request);

            $psrResponse = $handler->handle($psrRequest);

            $this->emitter->emit(
                $psrResponse,
                $response,
                in_array($request->getMethod(), self::BODY_PROHIBITED_REQUEST_METHODS, true)
            );
        });
        return $this;
    }
}
