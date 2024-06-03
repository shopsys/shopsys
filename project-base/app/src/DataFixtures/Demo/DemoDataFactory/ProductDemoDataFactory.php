<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DemoDataFactory;

use App\DataFixtures\Demo\UnitDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\Unit\Unit;
use DateTime;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class ProductDemoDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly ProductDataFactory $productDataFactory,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
    ) {
    }

    /**
     * @param string $catnum
     * @return \App\Model\Product\ProductData
     */
    public function createDefaultData(string $catnum): ProductData
    {
        $productData = $this->productDataFactory->create();

        $productData->catnum = $catnum;
        $productData->sellingDenied = false;

        $this->setVat($productData, VatDataFixture::VAT_HIGH);
        $this->setUnit($productData, UnitDataFixture::UNIT_PIECES);
        $this->setSellingTo($productData, null);

        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $vatReference
     */
    private function setVat(ProductData $productData, ?string $vatReference): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($vatReference !== null) {
                $productVatsIndexedByDomainId[$domainId] = $this->persistentReferenceFacade->getReferenceForDomain($vatReference, Domain::FIRST_DOMAIN_ID, Vat::class);
            }
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $unitReference
     */
    private function setUnit(ProductData $productData, string $unitReference): void
    {
        $productData->unit = $this->persistentReferenceFacade->getReference($unitReference, Unit::class);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string|null $date
     */
    private function setSellingTo(ProductData $productData, ?string $date): void
    {
        $productData->sellingTo = $date === null ? null : new DateTime($date);
    }
}
