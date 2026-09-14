<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

/**
 * What a medium is able to express, answerable without building anything — a declaration asks it before an
 * expression is ever built.
 */
interface ExpressionCapabilitiesInterface
{
    /**
     * @param string $operator One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     */
    public function supportsOperator(string $operator): bool;
}
