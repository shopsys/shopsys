<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Logger;

use Psr\Log\LoggerInterface;

class McpToolCallLogger
{
    public function __construct(protected readonly LoggerInterface $logger)
    {
    }

    /**
     * @param array<string, mixed> $inputContext
     * @param array<string, mixed> $resultContext
     */
    public function logSuccess(string $toolName, array $inputContext, array $resultContext, float $startedAt): void
    {
        $context = [
            'tool' => $toolName,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];

        if ($inputContext !== []) {
            $context['input'] = $inputContext;
        }

        if ($resultContext !== []) {
            $context['result'] = $resultContext;
        }

        $this->logger->info('MCP tool executed', $context);
    }

    /**
     * @param array<string, mixed> $inputContext
     */
    public function logRejected(string $toolName, array $inputContext, string $errorMessage, float $startedAt): void
    {
        $context = [
            'tool' => $toolName,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'error_message' => $errorMessage,
        ];

        if ($inputContext !== []) {
            $context['input'] = $inputContext;
        }

        $this->logger->warning('MCP tool rejected', $context);
    }
}
