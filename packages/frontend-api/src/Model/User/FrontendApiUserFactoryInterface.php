<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\UnencryptedToken;

interface FrontendApiUserFactoryInterface
{
    /**
     * @param \Lcobucci\JWT\UnencryptedToken $token
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    public function createFromToken(UnencryptedToken $token): FrontendApiUser;
}
