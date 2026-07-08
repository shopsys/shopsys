<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;
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
        protected readonly ProductPricesMulticurrencyModeProvider $productPricesMulticurrencyModeProvider,
    ) {
    }

    protected function createInstance(): ProductInputPriceData
    {
        return new ProductInputPriceData();
    }

    /**
     * @param array<int, array<string, \Shopsys\FrameworkBundle\Component\Money\Money|null>> $manualInputPricesByPricingGroupIdAndCurrencyCode
     */
    public function create(Vat $vat, array $manualInputPricesByPricingGroupIdAndCurrencyCode): ProductInputPriceData
    {
        $productInputPriceData = $this->createInstance();
        $productInputPriceData->vat = $vat;
        $productInputPriceData->manualInputPricesByPricingGroupIdAndCurrencyCode = $manualInputPricesByPricingGroupIdAndCurrencyCode;

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
                $this->getNullForPricingGroupsAndCurrencyCodesByDomainId($allPricingGroups, $domainId),
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

        $manualInputPrices = $this->productInputPriceFacade->getManualInputPricesDataIndexedByDomainIdPricingGroupIdAndCurrencyCode($product);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productInputPriceData[$domainId] = $this->create(
                $product->getVatForDomain($domainId),
                $manualInputPrices[$domainId] ?? [],
            );
        }

        return $productInputPriceData;
    }

    /**
     * @return string[]
     */
    protected function getEditableCurrencyCodesByDomainId(int $domainId): array
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            return $domainConfig->getCurrencyCodes();
        }

        return [$domainConfig->getDefaultCurrencyCode()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[] $allPricingGroups
     * @return array<int, array<string, null>>
     */
    protected function getNullForPricingGroupsAndCurrencyCodesByDomainId(array $allPricingGroups, int $domainId): array
    {
        $inputPrices = [];

        foreach ($allPricingGroups as $pricingGroup) {
            if ($pricingGroup->getDomainId() !== $domainId) {
                continue;
            }

            foreach ($this->getEditableCurrencyCodesByDomainId($domainId) as $currencyCode) {
                $inputPrices[$pricingGroup->getId()][$currencyCode] = null;
            }
        }

        return $inputPrices;
    }
}
