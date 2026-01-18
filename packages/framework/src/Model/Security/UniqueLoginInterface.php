<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

interface UniqueLoginInterface
{
    /**
     * @return string
     */
    public function getLoginToken();

    /**
     * @param string $loginToken
     */
    public function setLoginToken($loginToken): void;
}
