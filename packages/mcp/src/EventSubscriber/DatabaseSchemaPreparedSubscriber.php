<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\FrameworkBundle\Event\DatabaseSchemaPreparedEvent;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\User\McpReadOnlyUserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DatabaseSchemaPreparedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly McpReadOnlyUserManager $mcpReadOnlyUserManager,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
    }

    public function ensureMcpReadOnlyUser(DatabaseSchemaPreparedEvent $databaseSchemaPreparedEvent): void
    {
        if (!$this->mcpAvailabilityChecker->isAvailable()) {
            $databaseSchemaPreparedEvent->addMessage('MCP read-only database role was not prepared because MCP database credentials are not configured.');

            return;
        }

        $mcpDatabaseUser = $this->mcpReadOnlyUserManager->ensureReadOnlyUser();

        $databaseSchemaPreparedEvent->addMessage(sprintf('MCP read-only database role "%s" prepared.', $mcpDatabaseUser));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            DatabaseSchemaPreparedEvent::class => 'ensureMcpReadOnlyUser',
        ];
    }
}
