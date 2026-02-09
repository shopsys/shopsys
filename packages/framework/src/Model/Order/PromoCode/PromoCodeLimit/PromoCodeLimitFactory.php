<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PromoCodeLimitFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(string $from, string $discount): PromoCodeLimit
    {
        $className = $this->entityNameResolver->resolve(PromoCodeLimit::class);

        return new $className($from, $discount);
    }
}
