<?php

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
    public function setLoginToken(string $loginToken);

    /**
     * @return bool
     */
    public function isMultidomainLogin();

    /**
     * @param bool $multidomainLogin
     */
    public function setMultidomainLogin(bool $multidomainLogin);
}
