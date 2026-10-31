<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use LogicException;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Creates the state of a configured datagrid in the current request — the features narrowing the datagrid add
 * their parts by listening to `DatagridStateEvent`, the factory then combines their conditions.
 *
 * Extend the service in a project to combine them differently, for example to change which search outranks which.
 */
class DatagridStateFactory
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * The state is read from the main request, the one the framework grid reads its page and order from.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function create(DatagridConfig $config): DatagridState
    {
        $event = new DatagridStateEvent($config, $this->requestStack->getMainRequest());
        $this->eventDispatcher->dispatch($event);

        $parts = $event->getParts();
        $this->assertDomainControlIsApplied($config, $parts);
        $appliedSearch = $this->pickSearch($parts);

        return new DatagridState(
            $config,
            $parts,
            $this->createCondition($parts, $appliedSearch),
            $event->isInvalid(),
            $event->getAdditionalFields(),
            $appliedSearch,
        );
    }

    /**
     * Every scope combined by AND with the search that applies.
     *
     * @param array<class-string, object> $parts
     */
    protected function createCondition(array $parts, ?DatagridSearchInterface $appliedSearch): ?ConditionInterface
    {
        $conditions = [];

        foreach ($parts as $part) {
            if ($part instanceof DatagridScopeInterface) {
                $conditions[] = $part->createCondition();
            }
        }

        $conditions[] = $appliedSearch?->createCondition();
        $conditions = array_values(array_filter($conditions));

        return match (count($conditions)) {
            0 => null,
            1 => $conditions[0],
            default => Condition::andX(...$conditions),
        };
    }

    /**
     * The searches are several ways of asking the same question, so only one of the active ones applies — the first
     * one by `getSearchPrecedence()`. A search not ranked there comes after the ranked ones; among those, the one
     * added first (by the listener of the higher priority) applies.
     *
     * @param array<class-string, object> $parts
     */
    protected function pickSearch(array $parts): ?DatagridSearchInterface
    {
        $activeSearches = [];

        foreach ($parts as $partClass => $part) {
            if ($part instanceof DatagridSearchInterface && $part->isActive()) {
                $activeSearches[$partClass] = $part;
            }
        }

        foreach ($this->getSearchPrecedence() as $searchClass) {
            if (array_key_exists($searchClass, $activeSearches)) {
                return $activeSearches[$searchClass];
            }
        }

        return array_values($activeSearches)[0] ?? null;
    }

    /**
     * Any composed filter outranks the quick search — an invalid one narrows nothing and shows its errors instead
     * of quietly listing what the quick search asked for.
     *
     * @return list<class-string<\Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface>>
     */
    protected function getSearchPrecedence(): array
    {
        return [FilterPanel::class, QuickSearch::class];
    }

    /**
     * The domain control limits what the administrator may list at all, so a datagrid with a domain control is never
     * listed without it — not even when its subscriber is replaced by one that forgets to add it.
     *
     * @param array<class-string, object> $parts
     */
    protected function assertDomainControlIsApplied(DatagridConfig $config, array $parts): void
    {
        if ($config->domainControlConfig?->isEnabled() === true && array_key_exists(DomainControlScope::class, $parts) === false) {
            throw new LogicException(sprintf('The datagrid "%s" has a domain control, but no part of its state limits it to the domains - a subscriber adding "%s" has to be registered.', $config->gridId, DomainControlScope::class));
        }
    }
}
