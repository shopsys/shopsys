<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * A filter over a yes/no value — `BooleanFilter::new('isVerifiedPurchase', t('Verified purchase'))`.
 *
 * The value is the operation itself: the administrator picks "yes" or "no" and enters nothing more, so the rule
 * reads "Verified purchase — yes". Both operations compare by `equals`:
 *
 *     yes  →  Condition::equals('isVerifiedPurchase', true)
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
    public function getValueArity(string $operator): ExpressionValueArityEnum
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
     * "yes" and "no" are no operations of `ExpressionOperatorEnum`, so the applicability of `equals` decides
     * for both.
     */
    #[Override]
    protected function isOperatorApplicable(string $operator): bool
    {
        return parent::isOperatorApplicable(ExpressionOperatorEnum::EQUALS);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        return Condition::equals($this->path, $operator === self::OPERATOR_YES);
    }
}
