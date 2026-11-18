<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use DateTimeZone;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * What a filter needs to know about the datagrid it is declared on before it offers anything to the administrator.
 */
final readonly class FilterEnvironment
{
    public function __construct(
        public AdapterInterface $adapter,
        public ExpressionOperatorEnum $expressionOperatorEnum,
        public DateTimeZone $displayTimeZone,
    ) {
    }

    /**
     * Null when the adapter does not describe its paths.
     * Throws `PathNotFoundException` when the path leads nowhere.
     */
    public function describePath(string $path): ?PathDescription
    {
        return $this->adapter instanceof PathDescribingAdapterInterface ? $this->adapter->describePath($path) : null;
    }
}
