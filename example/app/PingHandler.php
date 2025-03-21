<?php

declare(strict_types=1);

namespace App;

use Laminas\Diactoros\Response\JsonResponse;

class PingHandler
{
    public function ping($pdo): JsonResponse
    {
        $connectionId = $pdo->query('SELECT CONNECTION_ID() AS cid')->fetchAll();

        return new JsonResponse([
            'message' => 'pong',
            'connectionId' => $connectionId,
        ]);
    }
}
