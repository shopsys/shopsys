<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;

class ProductAdditionalServiceDomainFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    protected function createInstance(
        Product $product,
        AdditionalService $additionalService,
        int $domainId,
    ): ProductAdditionalServiceDomain {
        $entityClassName = $this->entityNameResolver->resolve(ProductAdditionalServiceDomain::class);

        return new $entityClassName($product, $additionalService, $domainId);
    }

    public function create(
        Product $product,
        AdditionalService $additionalService,
        int $domainId,
    ): ProductAdditionalServiceDomain {
        return $this->createInstance($product, $additionalService, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[][] $additionalServicesIndexedByDomainId
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductAdditionalServiceDomain[]
     */
    public function createMultiple(
        Product $product,
        array $additionalServicesIndexedByDomainId,
    ): array {
        $productAdditionalServiceDomains = [];

        foreach ($additionalServicesIndexedByDomainId as $domainId => $additionalServicesOnDomain) {
            foreach ($additionalServicesOnDomain as $additionalService) {
                $productAdditionalServiceDomains[] = $this->create(
                    $product,
                    $additionalService,
                    $domainId,
                );
            }
        }

        return $productAdditionalServiceDomains;
    }
}
