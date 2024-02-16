<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GoPayPaymentMethodFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    public function create(GoPayPaymentMethodData $goPayPaymentMethodData): GoPayPaymentMethod
    {
        $className = $this->entityNameResolver->resolve(GoPayPaymentMethod::class);

        return new $className($goPayPaymentMethodData);
    }
}
