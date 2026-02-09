<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;

class ProductInputPriceDataFactory
{
    public function __construct(
        protected readonly ProductInputPriceFacade $productInputPriceFacade,
        protected readonly VatFacade $vatFacade,
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly Domain $domain,
    ) {
    }

    protected function createInstance(): ProductInputPriceData
    {
        return new ProductInputPriceData();
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Component\Money\Money|null> $manualInputPricesByPricingGroupId
     */
    public function create(Vat $vat, array $manualInputPricesByPricingGroupId): ProductInputPriceData
    {
        $productInputPriceData = $this->createInstance();
        $productInputPriceData->vat = $vat;
        $productInputPriceData->manualInputPricesByPricingGroupId = $manualInputPricesByPricingGroupId;

        return $productInputPriceData;
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\ProductInputPriceData>
     */
    public function createEmptyForAllDomains(): array
    {
        $productInputPriceData = [];
        $allPricingGroups = $this->pricingGroupFacade->getAll();

        foreach ($this->domain->getAllIds() as $domainId) {
            $productInputPriceData[$domainId] = $this->create(
                $this->vatFacade->getDefaultVatForDomain($domainId),
                $this->getNullForPricingGroupsByDomainId($allPricingGroups, $domainId),
            );
        }

        return $productInputPriceData;
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\ProductInputPriceData>
     */
    public function createFromProductForAllDomains(Product $product): array
    {
        $productInputPriceData = [];

        $manualInputPrices = $this->productInputPriceFacade->getManualInputPricesDataIndexedByDomainIdAndPricingGroupId($product);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productInputPriceData[$domainId] = $this->create(
                $product->getVatForDomain($domainId),
                $manualInputPrices[$domainId] ?? [],
            );
        }

        return $productInputPriceData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[] $allPricingGroups
     * @return array<int, null>
     */
    protected function getNullForPricingGroupsByDomainId(array $allPricingGroups, int $domainId): array
    {
        $inputPrices = [];

        foreach ($allPricingGroups as $pricingGroup) {
            if ($pricingGroup->getDomainId() === $domainId) {
                $inputPrices[$pricingGroup->getId()] = null;
            }
        }

        return $inputPrices;
    }
}
