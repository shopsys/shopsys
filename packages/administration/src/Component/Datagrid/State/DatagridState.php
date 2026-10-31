<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Webmozart\Assert\Assert;

/**
 * The state of one datagrid in the current request — the parts the features narrowing the datagrid added
 * (`DomainControlScope`, `QuickSearch`, `FilterPanel`, …) and the single condition they combine into.
 *
 * Created by `DatagridStateFactory`; `Datagrid::createView()` lists the records by it and the components of
 * the features render their parts.
 */
final readonly class DatagridState
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig $config Kept private — the state is rendered by components, and the configuration holds the adapter, which is none of their business
     * @param array<class-string, object> $parts
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null $condition Null lists every record
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $additionalFields Fields displayed on behalf of the features, see `DatagridStateEvent::addField()`
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface|null $appliedSearch The search whose condition applies, see `DatagridStateFactory::pickSearch()`
     */
    public function __construct(
        private DatagridConfig $config,
        private array $parts = [],
        public ?ConditionInterface $condition = null,
        private bool $invalid = false,
        public array $additionalFields = [],
        private ?DatagridSearchInterface $appliedSearch = null,
    ) {
    }

    /**
     * Whether the state was created from the given configuration, so that a datagrid is never listed by the state
     * of another one.
     */
    public function isCreatedFor(DatagridConfig $config): bool
    {
        return $this->config === $config;
    }

    /**
     * @template T of object
     * @param class-string<T> $partClass
     * @return T|null
     */
    public function get(string $partClass): ?object
    {
        $part = $this->parts[$partClass] ?? null;
        Assert::nullOrIsInstanceOf($part, $partClass);

        return $part;
    }

    /**
     * The search whose condition narrows the records, null when the administrator searched for nothing — the page
     * shows it as the one in use.
     */
    public function getAppliedSearch(): ?DatagridSearchInterface
    {
        return $this->appliedSearch;
    }

    /**
     * Whether the administrator narrowed the records by searching or filtering — the domain control is the scope
     * of the listing rather than a narrowing of it.
     */
    public function isNarrowedByAdministrator(): bool
    {
        return $this->appliedSearch !== null;
    }

    /**
     * Whether what the administrator asked for cannot be applied — the page answers 422 then.
     */
    public function isInvalid(): bool
    {
        return $this->invalid;
    }
}
