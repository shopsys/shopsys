<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Collator;
use Exception;
use Override;
use Shopsys\FrameworkBundle\Component\Context\AbstractContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Shopsys\FrameworkBundle\Component\Security\Role\Event\RolesCommandDetailEvent;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider;
use Shopsys\FrameworkBundle\Component\Security\Role\Section\RoleSection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'shopsys:rbac:roles',
    description: 'Display roles organized by context with their details and permissions',
)]
class RolesCommand extends Command
{
    protected const DETAIL_VIEW_THRESHOLD = 5;

    /**
     * @var array<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>, \Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider>
     */
    protected array $hierarchyProviders = [];

    /**
     * @var array<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>,\Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider>
     */
    protected array $roleSectionsProvidersByContext;

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Component\Security\Role\Hierarchy\AbstractRoleHierarchyProvider> $hierarchyProviders
     * @param iterable<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>,\Shopsys\FrameworkBundle\Component\Security\Role\Section\AbstractRoleSectionProvider> $roleSectionsProviders
     */
    public function __construct(
        protected readonly RoleRegistryInterface $roleRegistry,
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly EventDispatcherInterface $eventDispatcher,
        #[AutowireIterator('shopsys.role_hierarchy_provider')]
        iterable $hierarchyProviders,
        #[AutowireIterator('shopsys.role_section_provider', defaultIndexMethod: 'getTargetContext')]
        iterable $roleSectionsProviders = [],
    ) {
        parent::__construct();

        foreach ($hierarchyProviders as $provider) {
            $this->hierarchyProviders[$provider->getTargetContext()] = $provider;
        }

        $this->roleSectionsProvidersByContext = iterator_to_array($roleSectionsProviders);
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                'filter',
                InputArgument::OPTIONAL,
                'Filter roles by name or constant (case-insensitive)',
            )
            ->addOption(
                'context',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Show roles for specific context only (e.g., AdminContext)',
            )
            ->addOption(
                'hierarchy',
                null,
                InputOption::VALUE_NONE,
                'Show role hierarchy based on context',
            )
            ->setHelp(sprintf('Displays roles organized by context. Filter by name/constant (cannot be used with --hierarchy), use --context for specific context. Shows detailed view with routes when ≤%s roles found. Use --hierarchy to display role inheritance instead of roles.', static::DETAIL_VIEW_THRESHOLD));
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $contextFilter = $input->getOption('context');
        $searchFilter = $input->getArgument('filter');
        $showHierarchy = $input->getOption('hierarchy');

        if ($showHierarchy && $searchFilter) {
            $io->error('Cannot use filter argument with --hierarchy option. The hierarchy shows role relationships, not individual role details.');

            return Command::FAILURE;
        }

        $contexts = $this->getContexts($contextFilter, $io);

        if ($contexts === null) {
            return Command::FAILURE;
        }

        $hasRoles = false;

        foreach ($contexts as $context) {
            $roles = $this->loadAndFilterRoles($context->getIdentifier(), $searchFilter, $io);

            if ($roles === null) {
                continue;
            }

            $hasRoles = true;

            if ($showHierarchy) {
                $this->displayRoleHierarchy($io, $context);

                continue;
            }

            $this->displayRoles($io, $output, $context, $roles, $searchFilter);
        }

        if (!$hasRoles) {
            $io->info($searchFilter ? "No roles found matching filter \"{$searchFilter}\"." : 'No roles found.');
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>|null
     */
    protected function getContexts(?string $contextFilter, SymfonyStyle $io): ?array
    {
        $contexts = $this->contextResolver->getRegisteredContexts();

        if ($contextFilter === null) {
            return $contexts;
        }

        foreach ($contexts as $context) {
            $contextClass = $context->getIdentifier();
            $shortClassName = ReflectionHelper::getShortClassName($contextClass);

            if ($shortClassName === $contextFilter || $contextClass === $contextFilter) {
                return [$context];
            }
        }

        $io->error(sprintf('Context "%s" not found.', $contextFilter));
        $io->note('Available contexts: ' . implode(', ', array_map(
            fn (AbstractContext $context) => ReflectionHelper::getShortClassName($context->getIdentifier()),
            $contexts,
        )));

        return null;
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $contextClass
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Role\Role>|null
     */
    protected function loadAndFilterRoles(string $contextClass, ?string $searchFilter, SymfonyStyle $io): ?array
    {
        try {
            $roles = $this->roleRegistry->getRoles($contextClass);
        } catch (Exception $e) {
            $contextName = ReflectionHelper::getShortClassName($contextClass);
            $io->warning(sprintf('Could not load roles for context "%s": %s', $contextName, $e->getMessage()));

            return null;
        }

        if (count($roles) === 0) {
            return null;
        }

        if ($searchFilter === null) {
            return $roles;
        }

        $filterLower = strtolower($searchFilter);
        $filteredRoles = array_filter($roles, function (Role $role) use ($filterLower) {
            return str_contains(strtolower($role->getConstant()), $filterLower) ||
                   str_contains(strtolower($role->getName()), $filterLower);
        });

        return count($filteredRoles) === 0 ? null : $filteredRoles;
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     */
    protected function displayRoles(
        SymfonyStyle $io,
        OutputInterface $output,
        AbstractContext $context,
        array $roles,
        ?string $searchFilter,
    ): void {
        $io->title(sprintf('Roles for context: %s', ReflectionHelper::getShortClassName($context->getIdentifier())));
        $io->text(sprintf('Context description: %s', $context->getDescription()));

        if ($searchFilter !== null) {
            $io->text(sprintf('Filter: "%s"', $searchFilter));
        }
        $io->newLine();

        $collator = new Collator('en');
        usort($roles, fn (Role $a, Role $b) => $collator->compare($a->getName(), $b->getName()));

        if (count($roles) < static::DETAIL_VIEW_THRESHOLD) {
            $this->renderRoleDetails($io, $output, $roles, $context->getIdentifier());
        } else {
            $this->renderRolesTable($output, $roles, $context->getIdentifier());
        }

        $io->newLine();
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $contextClass
     */
    protected function renderRolesTable(OutputInterface $output, array $roles, string $contextClass): void
    {
        $table = new Table($output);
        $table->setHeaders(['Role Constant', 'Role Name', 'Section', 'Available Permissions']);

        $rows = [];

        foreach ($roles as $role) {
            $rows[] = [
                $role->getConstant(),
                $role->getName(),
                $this->getSection($role, $contextClass)->getName(),
                $this->formatPermissions($role->getAvailablePermissions()),
            ];
        }

        $table->setRows($rows);
        $table->render();
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Role> $roles
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $contextClass
     */
    protected function renderRoleDetails(
        SymfonyStyle $io,
        OutputInterface $output,
        array $roles,
        string $contextClass,
    ): void {
        foreach ($roles as $role) {
            $io->section($role->getName());

            $io->table(
                ['Property', 'Value'],
                [
                    ['Role Constant', $role->getConstant()],
                    ['Role Name', $role->getName()],
                    ['Section', $this->getSection($role, $contextClass)->getName()],
                    ['Available Permissions', $this->formatPermissions($role->getAvailablePermissions())],
                ],
            );

            $this->dispatchDetailEvent($io, $output, $role, $contextClass);
            $io->newLine();
        }
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $contextClass
     */
    protected function dispatchDetailEvent(
        SymfonyStyle $io,
        OutputInterface $output,
        Role $role,
        string $contextClass,
    ): void {
        $event = new RolesCommandDetailEvent($role, $contextClass);
        $this->eventDispatcher->dispatch($event);

        foreach ($event->getRenderCallbacks() as $callback) {
            $callback($io, $output);
        }
    }

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $contextClass
     */
    protected function getSection(Role $role, string $contextClass): RoleSection
    {
        if (isset($this->roleSectionsProvidersByContext[$contextClass])) {
            return $this->roleSectionsProvidersByContext[$contextClass]->getById($role->getRoleSection());
        }

        return AbstractRoleSectionProvider::getDefaultSection();
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Security\Role\Permission> $permissions
     */
    protected function formatPermissions(array $permissions): string
    {
        if (count($permissions) === 0) {
            return '-';
        }

        return implode(', ', array_map(
            fn (Permission $permission) => $permission->value,
            $permissions,
        ));
    }

    protected function displayRoleHierarchy(SymfonyStyle $io, AbstractContext $context): void
    {
        $contextClass = $context->getIdentifier();

        $io->title(sprintf('Role hierarchy for context: %s', ReflectionHelper::getShortClassName($contextClass)));
        $io->text(sprintf('Context description: %s', $context->getDescription()));
        $io->newLine();

        if (!isset($this->hierarchyProviders[$contextClass])) {
            $io->text('No role hierarchy defined for this context.');

            return;
        }

        $hierarchy = $this->hierarchyProviders[$contextClass]->generateRoleHierarchy();

        if (count($hierarchy) === 0) {
            $io->text('No role hierarchy defined for this context.');

            return;
        }

        $io->section('Role Hierarchy');

        foreach ($hierarchy as $parent => $children) {
            $io->text(sprintf('<info>%s</info>', $parent));

            foreach ($children as $child) {
                $io->text(sprintf('  └─ %s', $child));
            }

            $io->newLine();
        }
    }
}
