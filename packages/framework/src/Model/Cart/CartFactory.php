<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;

class CartFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function create(CustomerUserIdentifier $customerUserIdentifier): Cart
    {
        $entityClassName = $this->entityNameResolver->resolve(Cart::class);

        return new $entityClassName($customerUserIdentifier->getCartIdentifier(), $customerUserIdentifier->getCustomerUser());
    }
}
