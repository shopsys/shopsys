<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Enum;

enum EntityLogActionEnum: string implements EntityLogActionEnumInterface
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
}
