<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Enum;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class EntityLogActionEnum extends AbstractEnum
{
    public const string CREATE = 'create';
    public const string UPDATE = 'update';
    public const string DELETE = 'delete';
}
