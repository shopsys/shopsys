<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class StoreAvailabilityResolverMap extends ResolverMap
{
    /**
     * @var \Overblog\DataLoader\DataLoaderInterface
     */
    private DataLoaderInterface $storesBatchLoader;

    /**
     * @param \Overblog\DataLoader\DataLoaderInterface $storesBatchLoader
     */
    public function __construct(DataLoaderInterface $storesBatchLoader)
    {
        $this->storesBatchLoader = $storesBatchLoader;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'StoreAvailability' => [
                'exposed' => static fn ($storeAvailability) => $storeAvailability['exposed'],
                'availabilityInformation' => static fn ($storeAvailability) => $storeAvailability['availability_information'],
                'availabilityStatus' => static fn ($storeAvailability) => $storeAvailability['availability_status'],
                'store' => function ($storeAvailability) {
                    return $this->storesBatchLoader->load($storeAvailability['store_id']);
                },
            ],
        ];
    }
}
