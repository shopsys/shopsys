<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class LoginTypeEnum extends AbstractEnum
{
    public const string WEB = 'web';
    public const string FACEBOOK = 'facebook';
    public const string GOOGLE = 'google';
    public const string SEZNAM = 'seznam';
    public const string ADMIN = 'admin';
}
