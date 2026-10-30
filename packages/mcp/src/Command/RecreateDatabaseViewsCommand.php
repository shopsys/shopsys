<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Command;

use Override;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\View\McpDatabaseViewsFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:mcp:database-views:recreate',
    description: 'Recreates the database views with exposed columns that MCP queries run against. Migrations do it automatically.',
)]
class RecreateDatabaseViewsCommand extends Command
{
    public function __construct(
        protected readonly McpDatabaseViewsFacade $mcpDatabaseViewsFacade,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->mcpAvailabilityChecker->isAvailable()) {
            $this->mcpDatabaseViewsFacade->dropViews();
            $io->note(sprintf(
                'MCP database schema "%s" was dropped because MCP database credentials are not configured.',
                $this->mcpDatabaseViewsFacade->getViewsSchemaName(),
            ));

            return Command::SUCCESS;
        }

        $viewsCount = $this->mcpDatabaseViewsFacade->recreateViews();

        $io->success(sprintf(
            'MCP database schema "%s" was recreated with %d views of the exposed tables.',
            $this->mcpDatabaseViewsFacade->getViewsSchemaName(),
            $viewsCount,
        ));

        return Command::SUCCESS;
    }
}
