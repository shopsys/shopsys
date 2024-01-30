<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory;

class StoreFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursFactory $openingHoursFactory
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly OpeningHoursFactory $openingHoursFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreData $storeData
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function create(StoreData $storeData): Store
    {
        $entityClassName = $this->entityNameResolver->resolve(Store::class);

        /** @var \Shopsys\FrameworkBundle\Model\Store\Store $store */
        $store = new $entityClassName($storeData);

        $store->setOpeningHours(
            $this->createOpeningHours($storeData->openingHours, $store),
        );

        return $store;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursData
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours[]
     */
    protected function createOpeningHours(array $openingHoursData, Store $store): array
    {
        return array_map(
            function (OpeningHoursData $openingHourData) use ($store): OpeningHours {
                $openingHours = $this->openingHoursFactory->create($openingHourData);
                $openingHours->setStore($store);

                return $openingHours;
            },
            $openingHoursData,
        );
    }
}
