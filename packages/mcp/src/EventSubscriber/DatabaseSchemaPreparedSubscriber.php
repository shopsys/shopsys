<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\FrameworkBundle\Event\DatabaseSchemaPreparedEvent;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseUserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DatabaseSchemaPreparedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly McpDatabaseUserManager $mcpDatabaseUserManager,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
    }

    public function ensureMcpDatabaseUser(DatabaseSchemaPreparedEvent $databaseSchemaPreparedEvent): void
    {
        if (!$this->mcpAvailabilityChecker->isAvailable()) {
            $databaseSchemaPreparedEvent->addMessage('MCP database role was not prepared because MCP database credentials are not configured.');

            return;
        }

        $mcpDatabaseUser = $this->mcpDatabaseUserManager->ensureMcpUser();

        $databaseSchemaPreparedEvent->addMessage(sprintf('MCP database role "%s" prepared.', $mcpDatabaseUser));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            DatabaseSchemaPreparedEvent::class => 'ensureMcpDatabaseUser',
        ];
    }
}
