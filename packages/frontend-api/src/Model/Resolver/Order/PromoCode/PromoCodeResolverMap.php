<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order\PromoCode;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrontendApiBundle\Model\Order\PromoCode\PromoCodeWithDiscount;

class PromoCodeResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'PromoCode' => [
                'code' => function (PromoCodeWithDiscount $promoCodeWithDiscount) {
                    return $promoCodeWithDiscount->promoCode->getCode();
                },
                'type' => function (PromoCodeWithDiscount $promoCodeWithDiscount) {
                    return $promoCodeWithDiscount->promoCode->getDiscountType();
                },
            ],
        ];
    }
}
