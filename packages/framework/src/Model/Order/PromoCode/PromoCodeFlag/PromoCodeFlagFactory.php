<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class PromoCodeFlagFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(Flag $flag, string $type): PromoCodeFlag
    {
        $entityName = $this->entityNameResolver->resolve(PromoCodeFlag::class);

        return new $entityName($flag, $type);
    }
}
