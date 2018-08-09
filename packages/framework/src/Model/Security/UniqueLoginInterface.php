<?php

namespace Shopsys\FrameworkBundle\Model\Security;

interface UniqueLoginInterface
{
    public function getLoginToken();

    /**
     * @param string $loginToken
     */
    public function setLoginToken($loginToken);

    public function isMultidomainLogin();

    /**
     * @param bool $multidomainLogin
     */
    public function setMultidomainLogin($multidomainLogin);
}
