<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\FrameworkBundle\Event\DatabaseSchemaPreparedEvent;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseUserFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DatabaseSchemaPreparedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly McpDatabaseUserFacade $mcpDatabaseUserFacade,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
    }

    public function ensureMcpDatabaseUser(DatabaseSchemaPreparedEvent $databaseSchemaPreparedEvent): void
    {
        if (!$this->mcpAvailabilityChecker->isAvailable()) {
            $databaseSchemaPreparedEvent->addMessage('MCP database role was not prepared because MCP database credentials are not configured.');

            return;
        }

        $mcpDatabaseUser = $this->mcpDatabaseUserFacade->ensureMcpUser();

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
