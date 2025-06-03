<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:check-access-control-rules',
    description: 'Checks that all routes are covered by access control rules',
)]
class CheckAccessControlRulesCommand extends Command
{
    /**
     * @param string $adminUrl
     * @param string[] $excludedRouteNames
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlDataProvider $routeAccessControlDataProvider
     */
    public function __construct(
        protected readonly string $adminUrl,
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
        $routeNamesThatShouldBeCovered = $this->getRuteNamesThatShouldBeCovered();
        $actuallyCoveredRouteNames = $this->getActuallyCoveredRouteNames();

        $notCoveredRouteNames = array_diff($routeNamesThatShouldBeCovered, $actuallyCoveredRouteNames);

        if (count($notCoveredRouteNames) > 0) {
            $output->writeln('<error>Some routes are not covered by access control rules:</error>');

            foreach ($notCoveredRouteNames as $routeName) {
                $output->writeln(' - ' . $routeName);
            }
            $output->writeln('<info>Either add #[AccessControlRule] attribute to the corresponding controller action or add the routes to "shopsys.route_names_excluded_from_access_control_check" parameter.</info>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    protected function getRuteNamesThatShouldBeCovered(): array
    {
        $router = $this->domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID);
        $routeNamesThatShouldBeCovered = [];

        foreach ($router->getRouteCollection()->all() as $routeName => $route) {
            if (in_array($routeName, $this->excludedRouteNames, true) || !str_starts_with($route->getPath(), '/' . $this->adminUrl)) {
                continue;
            }
            $routeNamesThatShouldBeCovered[] = $routeName;
        }

        return $routeNamesThatShouldBeCovered;
    }

    /**
     * @return string[]
     */
    protected function getActuallyCoveredRouteNames(): array
    {
        $actuallyCoveredRouteNames = [];

        foreach ($this->routeAccessControlDataProvider->findAll() as $routeAccessControlRule) {
            $actuallyCoveredRouteNames[] = $routeAccessControlRule->routeName;
        }

        return $actuallyCoveredRouteNames;
    }
}
