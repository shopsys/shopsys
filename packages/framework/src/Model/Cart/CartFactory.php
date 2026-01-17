<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;

class CartFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(CustomerUserIdentifier $customerUserIdentifier): Cart
    {
        $entityClassName = $this->entityNameResolver->resolve(Cart::class);

        return new $entityClassName(
            $customerUserIdentifier->getCartIdentifier(),
            $customerUserIdentifier->getCustomerUser(),
        );
    }
}
