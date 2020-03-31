<?php

declare(strict_types = 1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\Token;

interface FrontendApiUserFactoryInterface
{
    /**
     * @param \Lcobucci\JWT\Token $token
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    public function createFromToken(Token $token): FrontendApiUser;
}
