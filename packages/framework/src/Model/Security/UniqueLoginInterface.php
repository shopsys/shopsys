<?php

namespace Shopsys\FrameworkBundle\Model\Security;

interface UniqueLoginInterface
{
    public function getLoginToken(): string;

    /**
     * @param string $loginToken
     */
    public function setLoginToken($loginToken);

    public function isMultidomainLogin(): bool;

    /**
     * @param bool $multidomainLogin
     */
    public function setMultidomainLogin($multidomainLogin);
}
