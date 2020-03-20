<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

class CustomerUserRefreshTokenChainsDataFactory implements CustomerUserRefreshTokenChainsDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainsData
     */
    public function create(): CustomerUserRefreshTokenChainsData
    {
        return new CustomerUserRefreshTokenChainsData();
    }
}
