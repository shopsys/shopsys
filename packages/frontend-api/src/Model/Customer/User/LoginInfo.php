<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

class LoginInfo
{
    /**
     * @param string $loginType
     * @param string|null $externalId
     */
    public function __construct(
        public readonly string $loginType,
        public readonly ?string $externalId,
    ) {
    }
}
