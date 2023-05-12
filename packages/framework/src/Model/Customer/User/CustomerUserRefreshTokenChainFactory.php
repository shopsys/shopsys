<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserRefreshTokenChainFactory implements CustomerUserRefreshTokenChainFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain
     */
    public function create(CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData): CustomerUserRefreshTokenChain
    {
        $classData = $this->entityNameResolver->resolve(CustomerUserRefreshTokenChain::class);

        return new $classData($customerUserRefreshTokenChainData);
    }
}
