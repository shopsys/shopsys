<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormTypeInterface;

interface AdvancedSearchFilterInterface
{
    public const string OPERATOR_CONTAINS = 'contains';
    public const string OPERATOR_NOT_CONTAINS = 'notContains';
    public const string OPERATOR_NOT_SET = 'notSet';
    public const string OPERATOR_NOT_REGISTERED = 'notRegistered';
    public const string OPERATOR_IS = 'is';
    public const string OPERATOR_IS_NOT = 'isNot';
    public const string OPERATOR_BEFORE = 'before';
    public const string OPERATOR_AFTER = 'after';
    public const string OPERATOR_GT = 'gt';
    public const string OPERATOR_GTE = 'gte';
    public const string OPERATOR_LT = 'lt';
    public const string OPERATOR_LTE = 'lte';
    public const string OPERATOR_EXISTS = 'exists';
    public const string OPERATOR_DOES_NOT_EXIST = 'doesNotExist';

    /**
     * Returns a unique name of the filter
     */
    public function getName(): string;

    /**
     * Returns an array of OPERATOR_* constants specifying which operators can be used with this filter
     *
     * @return string[]
     */
    public function getAllowedOperators(): array;

    /**
     * Returns a form type that should be used for value selection
     */
    public function getValueFormType(): FormTypeInterface|string;

    /**
     * Returns options that will be passed to the form type used for value selection
     */
    public function getValueFormOptions(): array;

    /**
     * Method that applies the filtering conditions specified by $rulesData to the provided query builder
     *
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData[] $rulesData
     */
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData);
}
