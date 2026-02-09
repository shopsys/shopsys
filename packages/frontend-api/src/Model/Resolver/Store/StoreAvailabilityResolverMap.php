<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;

class StoreAvailabilityResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly DataLoaderInterface $storesBatchLoader,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'StoreAvailability' => [
                'availabilityInformation' => static fn ($storeAvailability) => $storeAvailability['availability_information'],
                'availabilityStatus' => static fn ($storeAvailability) => $storeAvailability['availability_status'],
                'store' => function ($storeAvailability) {
                    return $this->storesBatchLoader->load($storeAvailability['store_id']);
                },
            ],
        ];
    }
}
