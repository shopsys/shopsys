<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

class AdministratorMcpTokenDataFactory
{
    protected function createInstance(): AdministratorMcpTokenData
    {
        return new AdministratorMcpTokenData();
    }

    public function create(): AdministratorMcpTokenData
    {
        return $this->createInstance();
    }
}
