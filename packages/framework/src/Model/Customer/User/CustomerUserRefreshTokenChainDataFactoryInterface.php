<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

interface CustomerUserRefreshTokenChainDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData
     */
    public function create(): CustomerUserRefreshTokenChainData;
}
