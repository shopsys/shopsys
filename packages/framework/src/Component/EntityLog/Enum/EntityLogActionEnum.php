<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Enum;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnumCasesProvider;

class EntityLogActionEnum extends AbstractEnumCasesProvider
{
    public const string CREATE = 'create';
    public const string UPDATE = 'update';
    public const string DELETE = 'delete';
}
