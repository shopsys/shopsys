<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;

final class DqlExpressionBuilderFactory implements DqlExpressionBuilderFactoryInterface
{
    /**
     * DQL expresses every operation of the vocabulary.
     *
     * @var string[]
     */
    private const array SUPPORTED_OPERATORS = [
        ExpressionOperatorEnum::EQUALS,
        ExpressionOperatorEnum::NOT_EQUALS,
        ExpressionOperatorEnum::CONTAINS,
        ExpressionOperatorEnum::STARTS_WITH,
        ExpressionOperatorEnum::ENDS_WITH,
        ExpressionOperatorEnum::GREATER_THAN,
        ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL,
        ExpressionOperatorEnum::LESS_THAN,
        ExpressionOperatorEnum::LESS_THAN_OR_EQUAL,
        ExpressionOperatorEnum::BETWEEN,
        ExpressionOperatorEnum::IN,
        ExpressionOperatorEnum::NOT_IN,
        ExpressionOperatorEnum::IS_NULL,
        ExpressionOperatorEnum::IS_NOT_NULL,
        ExpressionOperatorEnum::IS_EMPTY,
        ExpressionOperatorEnum::IS_NOT_EMPTY,
    ];

    public function __construct(
        private readonly ExpressionOperatorApplicability $expressionOperatorApplicability,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(ProxyQuery $proxyQuery): DqlExpressionBuilderInterface
    {
        return new DqlExpressionBuilder($proxyQuery, self::SUPPORTED_OPERATORS, $this->expressionOperatorApplicability);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supportsOperator(string $operator): bool
    {
        return in_array($operator, self::SUPPORTED_OPERATORS, true);
    }
}
