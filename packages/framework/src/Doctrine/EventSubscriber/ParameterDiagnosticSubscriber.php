<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Event\SchemaCreateTableEventArgs;
use Doctrine\DBAL\Event\SchemaDropTableEventArgs;
use Doctrine\DBAL\Event\SchemaAlterTableEventArgs;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Connection;

/**
 * Event subscriber for comprehensive parameter binding diagnostics
 */
class ParameterDiagnosticSubscriber implements EventSubscriberInterface, SQLLogger
{
    private array $queries = [];
    private bool $enabled = true;

    public function getSubscribedEvents(): array
    {
        return [
            Events::postConnect,
        ];
    }

    public function postConnect(ConnectionEventArgs $args): void
    {
        $connection = $args->getConnection();
        
        // Set this subscriber as the SQL logger
        $connection->getConfiguration()->setSQLLogger($this);
        
        error_log("🔧 [PARAM_DIAGNOSTIC] SQL Logger enabled for parameter binding diagnostics");
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $queryId = uniqid('query_', true);
        $this->queries[$queryId] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'start_time' => microtime(true),
        ];

        // Log comprehensive parameter information
        error_log("🚀 [PARAM_DIAGNOSTIC] Starting query: " . $this->sanitizeForLog($sql));
        
        if ($params !== null && !empty($params)) {
            error_log("🔗 [PARAM_DIAGNOSTIC] Parameters: " . json_encode($params));
        } else {
            error_log("🔗 [PARAM_DIAGNOSTIC] No parameters");
        }
        
        if ($types !== null && !empty($types)) {
            error_log("🔗 [PARAM_DIAGNOSTIC] Parameter types: " . json_encode($types));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery(): void
    {
        if (!$this->enabled || empty($this->queries)) {
            return;
        }

        $queryId = array_key_last($this->queries);
        $query = $this->queries[$queryId];
        $duration = microtime(true) - $query['start_time'];
        
        error_log("📊 [PARAM_DIAGNOSTIC] Query completed in " . round($duration * 1000, 2) . "ms");
        
        unset($this->queries[$queryId]);
    }

    /**
     * Sanitize SQL for logging (remove newlines, limit length)
     */
    private function sanitizeForLog(string $sql): string
    {
        $cleaned = preg_replace('/\s+/', ' ', trim($sql));
        return strlen($cleaned) > 300 ? substr($cleaned, 0, 300) . '...' : $cleaned;
    }
}