<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

class CustomerUserRefreshTokenChainDataFactory implements CustomerUserRefreshTokenChainDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData
     */
    public function create(): CustomerUserRefreshTokenChainData
    {
        return new CustomerUserRefreshTokenChainData();
    }
}
