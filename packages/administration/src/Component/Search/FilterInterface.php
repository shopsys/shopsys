<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Doctrine\ORM\QueryBuilder;

/**
 * One subject of the advanced search on a CRUD list page.
 * Implement it for a reusable filter, or use the generic Filter for an inline one.
 */
interface FilterInterface
{
    public function getName(): string;

    public function getLabel(): string;

    /**
     * @return \Shopsys\AdministrationBundle\Component\Search\Operator[]
     */
    public function getAllowedOperators(): array;

    /**
     * @return class-string<\Symfony\Component\Form\FormTypeInterface> Form type of the rule value widget
     */
    public function getValueFormType(): string;

    /**
     * @return array<string, mixed> Options for the rule value widget
     */
    public function getValueFormOptions(): array;

    /**
     * Extends the list query with the conditions of all rules of this filter.
     * The root alias of the query builder is always "o".
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void;
}
