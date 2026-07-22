<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdditionalServiceFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    protected function createInstance(AdditionalServiceData $additionalServiceData): AdditionalService
    {
        $entityClassName = $this->entityNameResolver->resolve(AdditionalService::class);

        return new $entityClassName($additionalServiceData);
    }

    public function create(AdditionalServiceData $additionalServiceData): AdditionalService
    {
        return $this->createInstance($additionalServiceData);
    }
}
