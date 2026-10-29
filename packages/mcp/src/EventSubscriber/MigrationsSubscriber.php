<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsFacade;
use Shopsys\MigrationBundle\Event\MigrationsFinishedEvent;
use Shopsys\MigrationBundle\Event\MigrationsStartingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MigrationsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly McpDatabaseViewsFacade $mcpDatabaseViewsFacade,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
    }

    public function dropMcpViews(): void
    {
        $this->mcpDatabaseViewsFacade->dropViews();
    }

    public function recreateMcpViews(): void
    {
        if ($this->mcpAvailabilityChecker->isAvailable()) {
            $this->mcpDatabaseViewsFacade->recreateViews();
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
