<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PromoCodeFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(PromoCodeData $data): PromoCode
    {
        $entityClassName = $this->entityNameResolver->resolve(PromoCode::class);

        return new $entityClassName($data);
    }
}
