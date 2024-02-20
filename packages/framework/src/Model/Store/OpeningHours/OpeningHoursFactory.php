<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Store\Store;

class OpeningHoursFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $data
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours
     */
    protected function create(OpeningHoursData $data): OpeningHours
    {
        $entityClassName = $this->entityNameResolver->resolve(OpeningHours::class);

        return new $entityClassName($data);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $data
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours
     */
    public function createWithStore(OpeningHoursData $data, Store $store): OpeningHours
    {
        $openingHours = $this->create($data);
        $openingHours->setStore($store);

        return $openingHours;
    }
}
