<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PromoCodeFactory implements PromoCodeFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $data
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    public function create(PromoCodeData $data): PromoCode
    {
        $classData = $this->entityNameResolver->resolve(PromoCode::class);

        return new $classData($data);
    }
}
