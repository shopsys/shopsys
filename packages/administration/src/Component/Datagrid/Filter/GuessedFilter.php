<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;

/**
 * Stands in the filters of a datagrid for the filter of a field declared `filterable`, under the filter name
 * of the field. The name, the path and the label are those of the field at the moment they are asked for.
 *
 * The filter itself is picked by `FilterGuesser` only when the state of the datagrid is created, so the methods
 * that need it throw. Declare the filter by `filters()->add()` to configure it, the declaration replaces this one.
 */
final class GuessedFilter implements FilterInterface
{
    public function __construct(
        public readonly FieldDescriptor $field,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return $this->field->getFilterName();
    }

    /**
     * The first property of the field. A field combining several properties fails when its filter is picked.
     */
    #[Override]
    public function getPath(): string
    {
        return $this->field->getProperties()[0];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return $this->field->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOperators(): array
    {
        throw $this->createNotPickedYetException();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOperatorLabel(string $operator): string
    {
        throw $this->createNotPickedYetException();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueArity(string $operator): ExpressionValueArityEnum
    {
        throw $this->createNotPickedYetException();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(ExpressionValueArityEnum $arity): string
    {
        throw $this->createNotPickedYetException();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(ExpressionValueArityEnum $arity): array
    {
        throw $this->createNotPickedYetException();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function resolveFor(FilterEnvironment $environment): void
    {
        throw $this->createNotPickedYetException();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildCondition(FilterRuleData $rule): ?ConditionInterface
    {
        throw $this->createNotPickedYetException();
    }

    private function createNotPickedYetException(): LogicException
    {
        return new LogicException(sprintf(
            'The filter of the field "%s" is picked only when the state of the datagrid is created, so it cannot be used or configured before that - declare the filter by filters()->add() to configure it.',
            $this->field->getName(),
        ));
    }
}
