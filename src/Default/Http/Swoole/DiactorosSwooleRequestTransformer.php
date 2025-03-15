<?php

declare(strict_types=1);

namespace RahimiAli\Hermes\Default\Http\Swoole;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;

/**
 * Transforms a swoole request to a ServerRequestInterface implemented by Diactoros
 *
 * @see https://docs.laminas.dev/laminas-diactoros Diactoros homepage
 * @see https://github.com/razonyang/psr7-swoole Some others guy implementation of this that heavily influenced this implementation
 */
class DiactorosSwooleRequestTransformer implements SwooleRequestTransformer
{
    public function transform(Request $swooleRequest): ServerRequestInterface
    {
        $server = array_change_key_case($swooleRequest->server, CASE_UPPER);
        $server['SCRIPT_NAME'] = $this->getScriptName();

        $files = $this->parseUploadedFiles($swooleRequest->files ?? []);

        $uri = $this->parseUri($server);

        $method = $swooleRequest->getMethod();

        $body = new Stream('php://memory', 'wb+');
        $body->write($swooleRequest->getContent() ?: '');
        $body->rewind();

        $headers = $swooleRequest->header ?? [];

        $cookies = $swooleRequest->cookie ?? [];

        parse_str($server['QUERY_STRING'] ?? '', $queryParams);

        return new ServerRequest(
            serverParams: $server,
            uploadedFiles: $files,
            uri: $uri,
            method: $method,
            body: $body,
            headers: $headers,
            cookieParams: $cookies,
            queryParams: $queryParams,
            parsedBody: null,
            protocol: $this->parseProtocol($server)
        );
    }

    /**
     * @param array<string, mixed> $server
     */
    private function parseProtocol(array $server): string
    {
        $defaultProtocol = '1.1';
        return isset($server['SERVER_PROTOCOL']) ? str_replace(
            'HTTP/',
            '',
            (string)$server['SERVER_PROTOCOL']
        ) : $defaultProtocol;
    }

    /**
     * @param array<string, mixed> $server
     */
    private function parseUri(array $server): Uri
    {
        $uri = new Uri();

        if ('off' !== ($server['HTTPS'] ?? false)) {
            $uri = $uri->withScheme('https');
        } else {
            $uri = $uri->withScheme('http');
        }

        if (isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort((int)$server['SERVER_PORT']);
        } else {
            $uri = $uri->withPort($uri->getScheme() === 'https' ? 443 : 80);
        }

        if (isset($server['HTTP_HOST'])) {
            $parts = explode(':', $server['HTTP_HOST']);
            $uri = count($parts) === 2
                ? $uri->withHost($parts[0])
                    ->withPort((int)$parts[1])
                : $uri->withHost($server['HTTP_HOST']);
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }

        if (isset($server['REQUEST_URI'])) {
            $uri = $uri->withPath($server['REQUEST_URI']);
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }

    /**
     * @param array<string, mixed>[] $files
     * @return UploadedFile[]
     */
    private function parseUploadedFiles(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            $uploadedFiles[] = new UploadedFile(
                $file['tmp_name'],
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type'],
            );
        }

        return $uploadedFiles;
    }

    private function getScriptName(): string
    {
        global $argv;

        return $argv[0] ?? '';
    }
}
