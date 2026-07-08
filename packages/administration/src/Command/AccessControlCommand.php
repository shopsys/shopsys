<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Command;

use Override;
use Shopsys\AdministrationBundle\Component\Configuration\AccessControlConfiguration;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\LiveComponentAccessControlDataProvider;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlDataProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:admin:access-control',
    description: 'Display and validate access control rules for admin routes',
)]
final class AccessControlCommand extends Command
{
    public function __construct(
        private readonly AccessControlConfiguration $accessControlConfiguration,
        private readonly RouteAccessControlDataProvider $routeAccessControlDataProvider,
        private readonly LiveComponentAccessControlDataProvider $liveComponentAccessControlDataProvider,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                'filter',
                InputArgument::OPTIONAL,
                'Filter routes by name or controller (supports wildcards like "admin_product_*" - use quotes for wildcards)',
            )
            ->addOption(
                'check',
                null,
                InputOption::VALUE_NONE,
                'Only check for uncovered routes and return error code for pipeline usage',
            )
            ->setHelp('Displays all admin routes categorized as Covered, Excluded, or Uncovered. Use --check for pipeline validation.');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filter = $input->getArgument('filter');
        $checkOnly = $input->getOption('check');

        $allRoutes = $this->routeAccessControlDataProvider->getAll();
        $allLiveComponentActions = $this->liveComponentAccessControlDataProvider->getAll();

        if ($checkOnly) {
            [, , $uncovered] = $this->categorizeRoutes($allRoutes);
            [, , $uncoveredLiveComponentActions] = $this->categorizeLiveComponentActions($allLiveComponentActions);

            if (count($uncovered) > 0 || count($uncoveredLiveComponentActions) > 0) {
                if (count($uncovered) > 0) {
                    $this->renderAccessControlDataTable($io, $output, 'Uncovered Routes', 'Route Name', $uncovered);
                }

                if (count($uncoveredLiveComponentActions) > 0) {
                    $this->renderAccessControlDataTable($io, $output, 'Uncovered Live Component Actions', 'Component Action', $uncoveredLiveComponentActions);
                }

                $io->error(sprintf(
                    'Found %d uncovered routes and %d uncovered Live Component actions',
                    count($uncovered),
                    count($uncoveredLiveComponentActions),
                ));
                $io->note('Fix by adding access control attributes to controller actions or Live Component actions, or exclude routes in configuration.');

                return Command::FAILURE;
            }

            $io->success('All routes are properly covered');

            return Command::SUCCESS;
        }

        if ($filter !== null) {
            $this->displayFilteredRoutes($io, $output, $allRoutes, $filter);

            return Command::SUCCESS;
        }

        return $this->displayAccessControlSections($io, $output, $allRoutes, $allLiveComponentActions) ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $allRoutes
     * @return array{array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>, array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>, array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>}
     */
    private function categorizeRoutes(array $allRoutes): array
    {
        $excludedRouteNames = $this->accessControlConfiguration->getExcludedRouteNames();
        $covered = [];
        $excluded = [];
        $uncovered = [];

        foreach ($allRoutes as $routeName => $routeData) {
            if (in_array($routeName, $excludedRouteNames, true)) {
                $excluded[$routeName] = $routeData;
            } elseif ($routeData->hasAnyRules()) {
                $covered[$routeName] = $routeData;
            } else {
                $uncovered[$routeName] = $routeData;
            }
        }

        return [$covered, $excluded, $uncovered];
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $allLiveComponentActions
     * @return array{array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>, array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>, array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>}
     */
    private function categorizeLiveComponentActions(array $allLiveComponentActions): array
    {
        $covered = [];
        $uncovered = [];

        foreach ($allLiveComponentActions as $actionName => $actionData) {
            if ($actionData->hasAnyRules()) {
                $covered[$actionName] = $actionData;
            } else {
                $uncovered[$actionName] = $actionData;
            }
        }

        return [$covered, [], $uncovered];
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $allRoutes
     */
    private function displayFilteredRoutes(
        SymfonyStyle $io,
        OutputInterface $output,
        array $allRoutes,
        string $filter,
    ): void {
        $filteredRoutes = $this->filterRoutes($allRoutes, $filter);

        $io->info(sprintf('Found %d routes matching filter "%s"', count($filteredRoutes), $filter));
        $io->newLine();

        if (count($filteredRoutes) > 0) {
            $this->renderAccessControlDataTable($io, $output, 'Matching Routes', 'Route Name', $filteredRoutes);
        }
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $routes
     * @return array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>
     */
    private function filterRoutes(array $routes, string $filter): array
    {
        $matchesFilter = str_contains($filter, '*')
            ? $this->createWildcardMatcher($filter)
            : $this->createContainsMatcher($filter);

        return array_filter($routes, $matchesFilter, ARRAY_FILTER_USE_BOTH);
    }

    private function createWildcardMatcher(string $filter): callable
    {
        $pattern = str_replace('*', '.*', $filter);
        $regex = "/^{$pattern}$/i";

        return fn (RouteAccessControlData $routeData, string $routeName): bool => preg_match($regex, $routeName) || preg_match($regex, $routeData->formatControllerInfo());
    }

    private function createContainsMatcher(string $filter): callable
    {
        $filterLower = strtolower($filter);

        return fn (RouteAccessControlData $routeData, string $routeName): bool => str_contains(strtolower($routeName), $filterLower) ||
            str_contains(strtolower($routeData->formatControllerInfo()), $filterLower);
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $allRoutes
     * @return bool True if there are uncovered routes
     */
    private function displayAccessControlSections(
        SymfonyStyle $io,
        OutputInterface $output,
        array $allRoutes,
        array $allLiveComponentActions,
    ): bool {
        $io->info(sprintf('Found %d total admin routes', count($allRoutes)));
        $io->newLine();

        [$covered, $excluded, $uncovered] = $this->categorizeRoutes($allRoutes);
        [$coveredLiveComponentActions, , $uncoveredLiveComponentActions] = $this->categorizeLiveComponentActions($allLiveComponentActions);

        if (count($covered) > 0) {
            $this->renderAccessControlDataTable($io, $output, 'Covered Routes', 'Route Name', $covered);
        }

        if (count($excluded) > 0) {
            $this->renderAccessControlDataTable($io, $output, 'Excluded Routes', 'Route Name', $excluded);
            $io->note('Excluded routes are intentionally ignored from access control checks (e.g., login pages, public endpoints)');
        }

        if (count($uncovered) > 0) {
            $this->renderAccessControlDataTable($io, $output, 'Uncovered Routes', 'Route Name', $uncovered);
            $io->note('Add access control attributes to controller actions or exclude routes in configuration.');
        }

        if (count($coveredLiveComponentActions) > 0) {
            $this->renderAccessControlDataTable($io, $output, 'Covered Live Component Actions', 'Component Action', $coveredLiveComponentActions);
        }

        if (count($uncoveredLiveComponentActions) > 0) {
            $this->renderAccessControlDataTable($io, $output, 'Uncovered Live Component Actions', 'Component Action', $uncoveredLiveComponentActions);
            $io->note('Add access control attributes to Live Component actions.');
        }

        return count($uncovered) > 0 || count($uncoveredLiveComponentActions) > 0;
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $accessControlData
     */
    private function renderAccessControlDataTable(
        SymfonyStyle $io,
        OutputInterface $output,
        string $title,
        string $nameHeader,
        array $accessControlData,
    ): void {
        $titleWithCount = sprintf('%s (%d)', $title, count($accessControlData));
        $io->section($titleWithCount);

        $rows = $this->prepareTableRows($accessControlData);

        $table = new Table($output);
        $table->setHeaders([$nameHeader, 'Controller', 'Required Roles']);
        $table->setRows($rows);
        $table->render();
        $io->newLine();
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData> $routes
     * @return array<array<string>>
     */
    private function prepareTableRows(array $routes): array
    {
        $rows = [];

        foreach ($routes as $routeName => $routeData) {
            $rows[] = [
                $routeName,
                $routeData->formatControllerInfo(),
                $this->formatRequiredRoles($routeData),
            ];
        }

        usort($rows, fn (array $a, array $b): int => strcasecmp($a[0], $b[0]));

        return $rows;
    }

    private function formatRequiredRoles(RouteAccessControlData $routeData): string
    {
        if (!$routeData->hasAnyRules()) {
            return '-';
        }

        $roleGroups = [];

        foreach ($routeData->accessControlRules as $rule) {
            $roleGroups[$rule->role->getConstant()][] = $rule->permission?->value;
        }

        return implode(', ', array_map(
            fn (string $roleConstant, array $permissions): string => $this->formatRoleWithPermissions($roleConstant, $permissions),
            array_keys($roleGroups),
            $roleGroups,
        ));
    }

    /**
     * @param array<string|null> $permissions
     */
    private function formatRoleWithPermissions(string $roleConstant, array $permissions): string
    {
        $uniquePermissions = array_unique(array_filter($permissions));

        $roleText = count($uniquePermissions) > 0
            ? $roleConstant . ' (' . implode(', ', $uniquePermissions) . ')'
            : $roleConstant;

        return $this->colorizeRole($roleText, $roleConstant);
    }

    private function colorizeRole(string $roleText, string $roleConstant): string
    {
        return match ($roleConstant) {
            'PUBLIC_ACCESS' => "<fg=red>{$roleText}</>",
            'ROLE_SUPER_ADMIN' => "<fg=magenta>{$roleText}</>",
            'ROLE_ADMIN' => "<fg=yellow>{$roleText}</>",
            default => $roleText,
        };
    }
}
