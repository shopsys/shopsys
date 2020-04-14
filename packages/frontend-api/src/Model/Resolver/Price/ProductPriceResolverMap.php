<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;

class ProductPriceResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'ProductPrice' => [
                'isPriceFrom' => function (ProductPrice $productPrice) {
                    return $productPrice->isPriceFrom();
                },
            ],
        ];
    }
}
