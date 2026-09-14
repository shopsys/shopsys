<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Symfony\Component\Form\FormInterface;

/**
 * The quick search of one datagrid — one text searched in every field declared `searchable`.
 *
 * Built by the datagrid from its fields and its request state; the component renders the form, the
 * condition narrows the records. The searched paths are validated against the adapter when the datagrid
 * builds it, so a search over a date or a number is refused before any query exists.
 */
final readonly class QuickSearch
{
    /**
     * @param string|null $term Null when nothing was searched for
     * @param array<string, string> $labelsByPath Labels of the searched fields by their path, for the placeholder of the input
     */
    public function __construct(
        public FormInterface $form,
        public ?string $term,
        public array $labelsByPath,
    ) {
    }

    /**
     * Some searched field contains the term; null when nothing was searched for.
     */
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
