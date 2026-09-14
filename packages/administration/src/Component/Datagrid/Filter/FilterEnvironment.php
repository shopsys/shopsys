<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use DateTimeZone;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * What a filter needs to know about the datagrid it is declared on before it offers anything to the
 * administrator — the medium, the rules of the vocabulary and the time zone of the administration.
 */
final readonly class FilterEnvironment
{
    public function __construct(
        public AdapterInterface $adapter,
        public ExpressionOperatorEnum $expressionOperatorEnum,
        public ExpressionOperatorApplicability $expressionOperatorApplicability,
        public DateTimeZone $displayTimeZone,
    ) {
    }

    /**
     * Null when the medium has no schema to describe the path by.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function describePath(string $path): ?PathDescription
    {
        return $this->adapter instanceof PathDescribingAdapterInterface ? $this->adapter->describePath($path) : null;
    }
}
