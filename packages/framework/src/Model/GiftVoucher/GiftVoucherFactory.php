<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class GiftVoucherFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(GiftVoucherData $giftVoucherData): GiftVoucher
    {
        $entityClassName = $this->entityNameResolver->resolve(GiftVoucher::class);

        return new $entityClassName($giftVoucherData);
    }
}
