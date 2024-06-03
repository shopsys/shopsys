<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DataSetter;

use App\Model\Product\ProductData;
use App\Model\Product\Unit\Unit;
use DateTime;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class ProductDemoDataSetter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
    ) {
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $vatReference
     */
    public function setVat(ProductData $productData, ?string $vatReference): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($vatReference !== null) {
                $productVatsIndexedByDomainId[$domainId] = $this->persistentReferenceFacade->getReferenceForDomain($vatReference, $domainId, Vat::class);
            }
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $unitReference
     */
    public function setUnit(ProductData $productData, string $unitReference): void
    {
        $productData->unit = $this->persistentReferenceFacade->getReference($unitReference, Unit::class);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $date
     */
    public function setSellingTo(ProductData $productData, ?string $date): void
    {
        $productData->sellingTo = $date === null ? null : new DateTime($date);
    }
}
