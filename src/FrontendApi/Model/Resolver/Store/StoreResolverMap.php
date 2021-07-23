<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Store;

use App\Model\Store\Store;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class StoreResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map()
    {
        return [
            'Store' => [
                'country' => static function (Store $store) {
                    return $store->getCountry()->getCode();
                },
            ],
        ];
    }
}
