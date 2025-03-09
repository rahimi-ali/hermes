<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Http;

use League\CLImate\CLImate;

readonly class CLIMateHttpKernelDebugLogger implements HttpKernelDebugLogger
{
    private const int COLS = 6;

    private const string BLANK = '    ';

    private CLImate $climate;

    public function __construct()
    {
        $this->climate = new CLImate();
    }

    public function workerStarted(string $workerId): void
    {
        $workerId = str_pad($workerId, 3, ' ', STR_PAD_BOTH);
        $this->climate->blue()->bold()->flank("Worker $workerId Started");
    }

    public function workerShutdown(string $workerId): void
    {
        $workerId = str_pad($workerId, 3, ' ', STR_PAD_BOTH);
        $this->climate->lightRed()->bold()->flank("Worker $workerId Shutdown");
    }

    public function requestReceived(string $workerId, string $requestId, string $method, string $path): void
    {
        $this->climate->lightBlue()->columns(
            ["W$workerId", "#$requestId", 'RECV', $method . $path, self::BLANK, self::BLANK],
            self::COLS,
        );
    }

    public function requestMatched(string $workerId, string $requestId, string $method, string $path, string $routeName): void
    {
        $this->climate->lightMagenta()->columns(
            ["W$workerId", "#$requestId", 'MTCH', $method . $path, $routeName, self::BLANK],
            self::COLS,
        );
    }

    public function responseSent(
        string $workerId,
        string $requestId,
        string $method,
        string $path,
        string $routeName,
        int $statusCode,
    ): void {
        $columns = ["W$workerId", "#$requestId", 'RESP', $method . $path, $routeName, '=> ' . $statusCode];
        if ($statusCode >= 400 && $statusCode < 500) {
            $this->climate->yellow()->columns($columns, self::COLS);
        } elseif ($statusCode >= 500) {
            $this->climate->red()->columns($columns, self::COLS);
        } else {
            $this->climate->green()->columns($columns, self::COLS);
        }
    }
}
