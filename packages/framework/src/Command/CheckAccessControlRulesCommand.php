<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlDataProvider;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:check-access-control-rules',
    description: 'Checks that all routes are covered by access control rules',
)]
class CheckAccessControlRulesCommand extends Command
{
    /**
     * @param string[] $excludedRouteNames
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlDataProvider $routeAccessControlDataProvider
     */
    public function __construct(
        protected readonly array $excludedRouteNames,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly RouteAccessControlDataProvider $routeAccessControlDataProvider,
    ) {
        parent::__construct();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get all registered uncovered routes (routes without access control rules)
        $allUncoveredRoutes = array_filter($this->routeAccessControlDataProvider->getAll(), fn (RouteAccessControlData $routeData) => !$routeData->hasAnyRules());

        // Remove excluded routes from uncovered routes
        $notCoveredRoutes = array_filter($allUncoveredRoutes, fn (RouteAccessControlData $routeData) => !in_array($routeData->routeName, $this->excludedRouteNames, true));

        if (count($notCoveredRoutes) > 0) {
            $io->error('Some routes are not covered by access control rules:');

            $tableData = [];

            foreach ($notCoveredRoutes as $routeData) {
                $tableData[] = [$routeData->routeName, $routeData->formatControllerInfo()];
            }

            // Sort by route name
            usort($tableData, fn ($a, $b) => strcmp($a[0], $b[0]));

            $io->table(['Route Name', 'Controller'], $tableData);

            $io->warning(sprintf('Found %d routes missing access control rules', count($notCoveredRoutes)));
            $io->note('Either add one of attribute from "Shopsys\FrameworkBundle\Component\Security" namespace to the corresponding controller action or add the routes to "shopsys.route_names_excluded_from_access_control_check" parameter.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
