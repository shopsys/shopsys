<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GoPayPaymentMethodFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(GoPayPaymentMethodData $goPayPaymentMethodData): GoPayPaymentMethod
    {
        $className = $this->entityNameResolver->resolve(GoPayPaymentMethod::class);

        return new $className($goPayPaymentMethodData);
    }
}
