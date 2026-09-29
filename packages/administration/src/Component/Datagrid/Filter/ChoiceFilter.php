<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * A filter over a value from a fixed set — a status, an enum:
 *
 *     ChoiceFilter::new('status', t('Status'))->setChoices([t('Pending') => 'pending', t('Approved') => 'approved'])
 */
class ChoiceFilter extends AbstractFilter
{
    /**
     * @var array<string|int, mixed>
     */
    protected array $choices = [];

    /**
     * @param array<string|int, mixed> $choices Labels to values, the way `ChoiceType` takes them
     * @return $this
     */
    public function setChoices(array $choices): static
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::IN,
            ExpressionOperatorEnum::NOT_IN,
            ExpressionOperatorEnum::EQUALS,
            ExpressionOperatorEnum::NOT_EQUALS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(string $operator): string
    {
        return ChoiceType::class;
    }

    /**
     * A list of values is picked from a multiple select, a single value from a plain one.
     *
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultValueFormOptions(string $operator): array
    {
        return [
            'choices' => $this->choices,
            'multiple' => $this->getValueArity($operator) === ExpressionValueArityEnum::LIST,
            'placeholder' => false,
        ];
    }
}
