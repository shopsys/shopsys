<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Filter on a field with a fixed set of values, offered as a select.
 * Multiple "is" rules combine as OR (IN), multiple "not" rules exclude all their values (NOT IN).
 */
final class ChoiceFilter extends AbstractFieldFilter
{
    /**
     * @var array<string, mixed>
     */
    private array $choices = [];

    /**
     * @param array<string, mixed> $choices Choices for the value select, labels as keys
     */
    public static function create(string $name, string $label, array $choices): self
    {
        $filter = new self($name, $label);
        $filter->choices = $choices;

        return $filter;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [Operator::IS, Operator::IS_NOT];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return ['choices' => $this->choices];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        InClauseHelper::extendQueryBuilderWithGroupedValues($queryBuilder, $this->getSingleFieldExpression(), $rules);
    }
}
