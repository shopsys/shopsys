<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * @internal this class is not intended to be used directly by other developers
 */
enum PartType
{
    case FIELD;
    case ASSOCIATION;
    case TRANSLATION;
}
