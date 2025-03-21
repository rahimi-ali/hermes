<?php

declare(strict_types=1);

namespace HermesFramework\Hermes\Default\Http;

use HermesFramework\Hermes\Core\Http\HttpKernelDebugger;
use HermesFramework\Hermes\Core\Http\MatchedHttpRoute;
use League\CLImate\CLImate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class CLIMateHttpKernelDebugger implements HttpKernelDebugger
{
    private const int COLS = 7;

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

    public function requestReceived(string $workerId, string $requestId, ServerRequestInterface $request): void
    {
        $this->climate->lightBlue()->columns(
            [
                "W$workerId",
                "#$requestId",
                'RECV',
                "{$request->getMethod()} {$request->getUri()->getPath()}",
                self::BLANK,
                self::BLANK,
                self::BLANK,
            ],
            self::COLS,
        );
    }

    public function requestMatched(
        string $workerId,
        string $requestId,
        ServerRequestInterface $request,
        MatchedHttpRoute $matchedRoue,
        int $matchTimeMicro,
    ): void {
        $this->climate->lightMagenta()->columns(
            [
                "W$workerId",
                "#$requestId",
                'MTCH',
                "{$request->getMethod()} {$request->getUri()->getPath()}",
                $matchedRoue->getName(),
                self::BLANK,
                self::formatMicroseconds($matchTimeMicro),
            ],
            self::COLS,
        );
    }

    public function responseSent(
        string $workerId,
        string $requestId,
        ServerRequestInterface $request,
        MatchedHttpRoute|null $matchedRoue,
        ResponseInterface $response,
        int $responseTimeMicro,
    ): void {
        $statusCode = $response->getStatusCode();

        $columns = [
            "W$workerId",
            "#$requestId",
            'RESP',
            "{$request->getMethod()} {$request->getUri()->getPath()}",
            $matchedRoue?->getName() ?? 'NONE',
            "=> $statusCode",
            self::formatMicroseconds($responseTimeMicro),
        ];
        if ($statusCode >= 400 && $statusCode < 500) {
            $this->climate->yellow()->columns($columns, self::COLS);
        } elseif ($statusCode >= 500) {
            $this->climate->red()->columns($columns, self::COLS);
        } else {
            $this->climate->green()->columns($columns, self::COLS);
        }
    }

    /**
     * Formats a microsecond time value into a human-readable string based on magnitude:
     * - Under 1ms: displays as microseconds with 'μs' symbol
     * - Under 100ms: displays as milliseconds with 2 decimal places
     * - Under 1s: displays as milliseconds without decimals
     * - 1s or more: displays as seconds with 2 decimal places
     */
    protected static function formatMicroseconds(int $microseconds): string
    {
        if ($microseconds < 1000) {
            return round($microseconds) . 'μs';
        }

        if ($microseconds < 100 * 1000) {
            return number_format($microseconds / 1000, 2) . 'ms';
        }

        if ($microseconds < 1000 * 1000) {
            return round($microseconds / 1000) . 'ms';
        }

        return number_format($microseconds / 1000000, 2) . 's';
    }
}
