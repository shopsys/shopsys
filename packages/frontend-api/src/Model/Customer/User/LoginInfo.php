<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

class LoginInfo
{
    public function __construct(
        public readonly string $loginType,
        public readonly ?string $externalId,
    ) {
    }
}
