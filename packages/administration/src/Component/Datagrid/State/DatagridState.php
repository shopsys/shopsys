<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Webmozart\Assert\Assert;

/**
 * The state of one datagrid in the current request. It holds the parts the features narrowing the datagrid added
 * (`DomainControlScope`, `QuickSearch`, `FilterPanel`, …) and the single condition they combine into.
 */
final readonly class DatagridState
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig $config The configuration the state was created for, see `isCreatedFor()`
     * @param array<class-string, object> $parts
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null $condition Null lists every record
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $additionalFields Fields displayed on behalf of the features, see `DatagridStateEvent::addField()`
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
     * The search whose condition narrows the records, null when the administrator searched for nothing.
     */
    public function getAppliedSearch(): ?DatagridSearchInterface
    {
        return $this->appliedSearch;
    }

    /**
     * Whether the administrator narrowed the records by searching or filtering. The domain control is not counted,
     * as it is the scope of the listing rather than a narrowing of it.
     */
    public function isNarrowedByAdministrator(): bool
    {
        return $this->appliedSearch !== null;
    }

    public function isInvalid(): bool
    {
        return $this->invalid;
    }
}
