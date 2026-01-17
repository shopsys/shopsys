<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserRefreshTokenChainFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData,
    ): CustomerUserRefreshTokenChain {
        $entityClassName = $this->entityNameResolver->resolve(CustomerUserRefreshTokenChain::class);

        return new $entityClassName($customerUserRefreshTokenChainData);
    }
}
