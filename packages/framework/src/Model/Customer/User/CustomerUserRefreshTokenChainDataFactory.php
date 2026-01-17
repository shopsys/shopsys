<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

class CustomerUserRefreshTokenChainDataFactory
{
    protected function createInstance(): CustomerUserRefreshTokenChainData
    {
        return new CustomerUserRefreshTokenChainData();
    }

    public function create(): CustomerUserRefreshTokenChainData
    {
        return $this->createInstance();
    }
}
