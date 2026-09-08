<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;

final class FilterRule
{
    public function __construct(
        public readonly Operator $operator,
        public readonly mixed $value,
        private readonly string $formIndex,
        private readonly string $parameterPrefix,
    ) {
    }

    /**
     * Returns the index of the rule in the advanced search form, used to map errors back to the rule row.
     */
    public function getFormIndex(): string
    {
        return $this->formIndex;
    }

    /**
     * Returns a query parameter name that is unique per rule and hint, so filters never have to manage
     * parameter name suffixes themselves. The same hint returns the same name within one rule.
     */
    public function param(string $hint = 'value'): string
    {
        return preg_replace('/\W/', '_', "{$this->parameterPrefix}_{$this->formIndex}_{$hint}");
    }

    /**
     * Returns the rule value as a LIKE pattern for substring matching, with "*" / "?" wildcards translated
     * and SQL wildcard characters escaped.
     */
    public function getLikeValue(): string
    {
        return '%' . DatabaseSearchingHelper::getLikeSearchString((string)$this->value) . '%';
    }
}
