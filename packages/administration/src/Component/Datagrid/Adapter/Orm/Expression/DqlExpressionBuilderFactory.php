<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;

final class DqlExpressionBuilderFactory implements DqlExpressionBuilderFactoryInterface
{
    public function __construct(
        private readonly ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(ProxyQuery $proxyQuery): DqlExpressionBuilderInterface
    {
        return new DqlExpressionBuilder($proxyQuery, $this->expressionOperatorEnum);
    }
}
