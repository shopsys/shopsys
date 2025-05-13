<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart\PromoCode;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    #[Override]
    protected function map(): array
    {
        return [
            'PromoCode' => [
                'type' => function (PromoCode $promoCode) {
                    return $promoCode->getDiscountType();
                },
            ],
        ];
    }
}
