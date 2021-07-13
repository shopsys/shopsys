<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Store;

use Overblog\GraphQLBundle\Resolver\ResolverMap;

class StoreAvailabilityResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'StoreAvailability' => [
                'storeName' => static fn ($storeAvailability) => $storeAvailability['store_name'],
                'exposed' => static fn ($storeAvailability) => $storeAvailability['exposed'],
                'availabilityInformation' => static fn ($storeAvailability) => $storeAvailability['availability_information'],
                'availabilityStatus' => static fn ($storeAvailability) => $storeAvailability['availability_status'],
            ],
        ];
    }
}
