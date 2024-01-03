<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Enum;

enum EntityLogSourceEnum: string implements EntityLogSourceEnumInterface
{
    case USER = 'user';
    case ADMIN = 'admin';
    case API = 'api';
    case SYSTEM = 'system';
}
