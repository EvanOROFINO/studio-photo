<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Lightweight healthcheck for uptime monitors (UptimeRobot, BetterStack, etc.).
 * Returns 200 if the database is reachable, 503 otherwise.
 */
class HealthController extends AbstractController
{
    #[Route('/health', name: 'app_health', methods: ['GET'])]
    public function check(Connection $connection): JsonResponse
    {
        $dbStatus = 'ok';
        $statusCode = 200;

        try {
            $connection->executeQuery('SELECT 1');
        } catch (\Throwable) {
            $dbStatus = 'down';
            $statusCode = 503;
        }

        return new JsonResponse([
            'status' => $statusCode === 200 ? 'ok' : 'degraded',
            'database' => $dbStatus,
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ], $statusCode);
    }
}
