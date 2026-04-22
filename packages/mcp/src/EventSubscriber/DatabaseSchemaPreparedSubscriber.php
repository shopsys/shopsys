<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\FrameworkBundle\Event\DatabaseSchemaPreparedEvent;
use Shopsys\McpBundle\Component\Database\User\McpReadOnlyUserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DatabaseSchemaPreparedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly McpReadOnlyUserManager $mcpReadOnlyUserManager,
    ) {
    }

    public function ensureMcpReadOnlyUser(DatabaseSchemaPreparedEvent $databaseSchemaPreparedEvent): void
    {
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
