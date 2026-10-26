<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsManager;
use Shopsys\MigrationBundle\Event\MigrationsFinishedEvent;
use Shopsys\MigrationBundle\Event\MigrationsStartingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MigrationsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly McpDatabaseViewsManager $mcpDatabaseViewsManager,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
    }

    /**
     * The views are dropped even when MCP is not available (anymore), otherwise views left from the time when it was
     * would block the migrations that drop or alter their columns.
     */
    public function dropMcpViews(): void
    {
        $this->mcpDatabaseViewsManager->dropViews();
    }

    public function recreateMcpViews(): void
    {
        if ($this->mcpAvailabilityChecker->isAvailable()) {
            $this->mcpDatabaseViewsManager->recreateViews();
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            MigrationsStartingEvent::class => 'dropMcpViews',
            MigrationsFinishedEvent::class => 'recreateMcpViews',
        ];
    }
}
