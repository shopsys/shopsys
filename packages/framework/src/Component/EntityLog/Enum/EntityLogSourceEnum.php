<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Enum;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class EntityLogSourceEnum extends AbstractEnum
{
    public const string USER = 'user';
    public const string ADMIN = 'admin';
    public const string API = 'api';
    public const string SYSTEM = 'system';
}
