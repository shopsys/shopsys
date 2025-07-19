<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ConnectionInitializationSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postConnect,
        ];
    }

    public function postConnect(ConnectionEventArgs $args): void
    {
        $connection = $args->getConnection();
        $dbName = $connection->getDatabase(); // This is the key: forces full initialization.

        // Always log this to verify the fix is working
        error_log("🔧 [CONNECTION_FIX] Doctrine connection automatically initialized: {$dbName}");
        
        $this->logger->debug('Doctrine connection forced to fully initialize.', [
            'database' => $dbName,
            'driver' => $connection->getDriver()::class,
            'host' => $connection->getParams()['host'] ?? 'unknown',
        ]);
    }
}