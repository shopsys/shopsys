<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * A filter over a yes/no value — `BooleanFilter::new('isVerifiedPurchase', t('Verified purchase'))`.
 *
 * The value is the operation itself: the administrator picks "yes" or "no" and there is nothing more to
 * enter, so the rule reads "Verified purchase — yes". Both operations compare by `equals` in the vocabulary.
 */
class BooleanFilter extends AbstractFilter
{
    public const string OPERATOR_YES = 'yes';
    public const string OPERATOR_NO = 'no';

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            self::OPERATOR_YES,
            self::OPERATOR_NO,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOperatorLabel(string $operator): string
    {
        return match ($operator) {
            self::OPERATOR_YES => t('Yes'),
            self::OPERATOR_NO => t('No'),
            default => parent::getOperatorLabel($operator),
        };
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueArity(string $operator): string
    {
        return ExpressionValueArityEnum::NONE;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getApplicableValueTypes(): array
    {
        return [PathValueTypeEnum::BOOLEAN];
    }

    /**
     * Both operations compare by equality, so its applicability decides — "yes" and "no" are no operations of
     * the vocabulary.
     */
    #[Override]
    protected function isOperatorApplicable(string $operator, FilterEnvironment $environment): bool
    {
        return parent::isOperatorApplicable(ExpressionOperatorEnum::EQUALS, $environment);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildCondition(FilterRuleData $rule): ?ConditionInterface
    {
        if (in_array($rule->operator, $this->getOperators(), true) === false) {
            return null;
        }

        return Condition::equals($this->path, $rule->operator === self::OPERATOR_YES);
    }
}
