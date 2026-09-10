<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\GiftVoucher;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherValueCalculation;

class AppliedGiftVoucherResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly GiftVoucherValueCalculation $giftVoucherValueCalculation,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'AppliedGiftVoucher' => [
                'valueWithoutVat' => fn (GiftVoucher $giftVoucher) => $this->giftVoucherValueCalculation->calculateValueWithoutVat($giftVoucher),
            ],
        ];
    }
}
