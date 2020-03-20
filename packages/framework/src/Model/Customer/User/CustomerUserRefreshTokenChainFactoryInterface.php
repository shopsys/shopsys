<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

interface CustomerUserRefreshTokenChainFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData
     */
    public function create(CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData);
}
