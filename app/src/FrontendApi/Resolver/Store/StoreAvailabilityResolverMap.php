<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store;

use App\Model\Store\Exception\StoreNotFoundException;
use App\Model\Store\StoreFacade;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StoreAvailabilityResolverMap extends ResolverMap
{
    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(StoreFacade $storeFacade, Domain $domain)
    {
        $this->storeFacade = $storeFacade;
        $this->domain = $domain;
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
                    try {
                        return $this->storeFacade->getByIdEnabledOnDomain(
                            $storeAvailability['store_id'],
                            $this->domain->getId()
                        );
                    } catch (StoreNotFoundException $e) {
                        return null;
                    }
                },
            ],
        ];
    }
}
