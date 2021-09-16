<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Customer;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;

class DeliveryAddressResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'DeliveryAddress' => [
                'country' => static function (DeliveryAddress $deliveryAddress) {
                    return $deliveryAddress->getCountry()->getCode();
                },
            ],
        ];
    }
}
