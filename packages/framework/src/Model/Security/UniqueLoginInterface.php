<?php

namespace Shopsys\FrameworkBundle\Model\Security;

interface UniqueLoginInterface
{
    public function getLoginToken(): string;
    
    public function setLoginToken(string $loginToken): void;

    public function isMultidomainLogin(): bool;
    
    public function setMultidomainLogin(bool $multidomainLogin): void;
}
