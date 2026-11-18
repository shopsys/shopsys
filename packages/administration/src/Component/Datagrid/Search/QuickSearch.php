<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface;

/**
 * The quick search of one datagrid, one text searched in every field declared `searchable`.
 */
final readonly class QuickSearch implements DatagridSearchInterface
{
    /**
     * @param string|null $term The submitted text, null when nothing was searched for
     * @param list<\Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchedPath> $searchedPaths
     */
    public function __construct(
        public string $formName,
        public ?string $term,
        public array $searchedPaths,
    ) {
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->term !== null;
    }

    /**
     * The labels of the searched fields for the placeholder of the input. A field searched in several paths is named
     * once.
     *
     * @return string[]
     */
    public function getLabels(): array
    {
        return array_values(array_unique(array_map(
            static fn (SearchedPath $searchedPath): string => $searchedPath->label,
            $this->searchedPaths,
        )));
    }

    /**
     * Some searched path matches the term, see `createComparison()`. With no path left, nothing is found.
     */
    #[Override]
    public function createCondition(): ?ConditionInterface
    {
        if ($this->term === null) {
            return null;
        }

        $comparisons = [];

        foreach ($this->searchedPaths as $searchedPath) {
            $comparison = $this->createComparison($searchedPath, $this->term);

            if ($comparison !== null) {
                $comparisons[] = $comparison;
            }
        }

        return Condition::orX(...$comparisons);
    }

    /**
     * Null skips the path:
     *
     * - a path leading to a UUID equals the term;
     * - a path leading to a UUID is skipped when the term is no UUID, as the database refuses to compare the two;
     * - any other path contains the term.
     */
    private function createComparison(SearchedPath $searchedPath, string $term): ?ConditionInterface
    {
        if ($searchedPath->valueType !== PathValueTypeEnum::GUID) {
            return Condition::contains($searchedPath->path, $term);
        }

        if (Uuid::isValid($term) === false) {
            return null;
        }

        // the canonical form, as the database refuses a prefix like "urn:uuid:" that the validation accepts
        return Condition::equals($searchedPath->path, Uuid::fromString($term)->toString());
    }
}
