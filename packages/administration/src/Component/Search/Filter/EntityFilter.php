<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Filter on a to-one association, offering the related entities as a select.
 * Multiple "is" rules combine as OR (IN), multiple "not" rules exclude all their values (NOT IN).
 */
final class EntityFilter extends AbstractFieldFilter
{
    /**
     * @var class-string
     */
    private string $entityClass;

    /**
     * @var string|callable|null
     */
    private mixed $choiceLabel = null;

    /**
     * @param class-string $entityClass Class of the related entity offered in the value select
     */
    public static function create(string $name, string $label, string $entityClass): self
    {
        $filter = new self($name, $label);
        $filter->entityClass = $entityClass;

        return $filter;
    }

    /**
     * Sets the entity property (or a callable) used as the option label in the value select.
     *
     * @param string|callable(object): string $choiceLabel
     */
    public function choiceLabel(string|callable $choiceLabel): self
    {
        $this->choiceLabel = $choiceLabel;

        return $this;
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
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        $options = ['class' => $this->entityClass];

        if ($this->choiceLabel !== null) {
            $options['choice_label'] = $this->choiceLabel;
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        if ($this->expression !== null || count($this->fieldPaths) !== 1) {
            throw new LogicException(sprintf('Filter "%s" supports exactly one searched association field.', $this->name));
        }

        $fieldExpression = $this->getProxyQuery()->getAssociationTargetExpression($this->fieldPaths[0]);

        InClauseHelper::extendQueryBuilderWithGroupedValues($queryBuilder, $fieldExpression, $rules);
    }
}
