<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Logger;

use Psr\Log\LoggerInterface;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequest;
use Throwable;

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
        $context = $this->createContext($toolName, $startedAt, $inputContext);

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
        $context = $this->createContext($toolName, $startedAt, $inputContext);

        $context['error_message'] = $errorMessage;

        $this->logger->warning('MCP tool rejected', $context);
    }

    /**
     * @param array<string, mixed> $inputContext
     * @return array{tool: string, duration_ms: float, input: array}
     */
    protected function createContext(string $toolName, float $startedAt, array $inputContext): array
    {
        $context = [
            'tool' => $toolName,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];

        if ($inputContext !== []) {
            $context['input'] = $inputContext;
        }

        return $context;
    }

    public function logChangeRequestCreated(McpChangeRequest $mcpChangeRequest): void
    {
        $context = $this->createChangeRequestContext($mcpChangeRequest);
        $context['input'] = $mcpChangeRequest->getArguments();

        $this->logger->info('MCP change request created', $context);
    }

    public function logChangeRequestRefreshed(McpChangeRequest $mcpChangeRequest): void
    {
        $this->logger->info('MCP change request refreshed', $this->createChangeRequestContext($mcpChangeRequest));
    }

    public function logChangeRequestApproved(McpChangeRequest $mcpChangeRequest): void
    {
        $this->logger->info('MCP change request approved', $this->createChangeRequestContext($mcpChangeRequest));
    }

    public function logChangeRequestDiscarded(McpChangeRequest $mcpChangeRequest): void
    {
        $this->logger->info('MCP change request discarded', $this->createChangeRequestContext($mcpChangeRequest));
    }

    public function logChangeRequestFailed(McpChangeRequest $mcpChangeRequest, Throwable $exception): void
    {
        $context = $this->createChangeRequestContext($mcpChangeRequest);
        $context['status'] = $mcpChangeRequest->getStatus();
        $context['error_message'] = $mcpChangeRequest->getErrorMessage();
        $context['exception'] = $exception;

        $this->logger->warning('MCP change request failed', $context);
    }

    /**
     * @return array{tool: string, change_request_id: int, administrator_id: int}
     */
    protected function createChangeRequestContext(McpChangeRequest $mcpChangeRequest): array
    {
        return [
            'tool' => $mcpChangeRequest->getToolName(),
            'change_request_id' => $mcpChangeRequest->getId(),
            'administrator_id' => $mcpChangeRequest->getAdministrator()->getId(),
        ];
    }
}
