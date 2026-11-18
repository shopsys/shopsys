<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface;

/**
 * The quick search of one datagrid — one text searched in every field declared `searchable`.
 *
 * A part of the state of the datagrid added by `QuickSearchStateSubscriber`; the component builds the form from it,
 * the condition narrows the records. The searched paths are validated against the adapter by
 * `SearchablePathResolver`, so a search over a date or a number is refused before any query exists.
 */
final readonly class QuickSearch implements DatagridSearchInterface
{
    /**
     * @param string $formName The name of the GET form the text is searched by, unique for the datagrid
     * @param string|null $term The submitted text, null when nothing was searched for
     * @param array<string, string> $labelsByPath Labels of the searched fields by the paths searched in
     */
    public function __construct(
        public string $formName,
        public ?string $term,
        public array $labelsByPath,
    ) {
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->term !== null;
    }

    /**
     * The labels of the searched fields for the placeholder of the input, each once — a field combining several
     * properties is searched in every one of them leading to text.
     *
     * @return string[]
     */
    public function getLabels(): array
    {
        return array_values(array_unique($this->labelsByPath));
    }

    /**
     * Some searched field contains the term; null when nothing was searched for.
     */
    #[Override]
    public function createCondition(): ?ConditionInterface
    {
        if ($this->term === null) {
            return null;
        }

        return Condition::orX(...array_map(
            fn (string $path): Comparison => Condition::contains($path, $this->term),
            array_keys($this->labelsByPath),
        ));
    }
}
