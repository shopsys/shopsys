<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Store\Store;

class OpeningHoursFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    protected function create(OpeningHoursData $data): OpeningHours
    {
        $entityClassName = $this->entityNameResolver->resolve(OpeningHours::class);

        return new $entityClassName($data);
    }

    public function createWithStore(OpeningHoursData $data, Store $store): OpeningHours
    {
        $openingHours = $this->create($data);
        $openingHours->setStore($store);

        return $openingHours;
    }
}
