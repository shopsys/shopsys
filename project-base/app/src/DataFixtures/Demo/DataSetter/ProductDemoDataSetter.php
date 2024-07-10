<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DataSetter;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use App\Model\Product\ProductData;
use DateTime;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory;
use Shopsys\FrameworkBundle\Model\Stock\StockRepository;

class ProductDemoDataSetter
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockRepository $stockRepository
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockDataFactory $productStockDataFactory
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly ProductParameterValueDataFactory $productParameterValueDataFactory,
        private readonly ParameterValueDataFactory $parameterValueDataFactory,
        private readonly PriceConverter $priceConverter,
        private readonly StockRepository $stockRepository,
        private readonly ProductStockDataFactory $productStockDataFactory,
    ) {
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $vatReference
     */
    public function setVat(ProductData $productData, string $vatReference): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->persistentReferenceFacade->getReferenceForDomain($vatReference, $domainId, Vat::class);
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
     * @param string[] $flagReferences
     */
    public function setFlags(ProductData $productData, array $flagReferences): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($flagReferences as $flagReference) {
                $productData->flagsByDomainId[$domainId][] = $this->persistentReferenceFacade->getReference($flagReference, Flag::class);
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $date
     */
    public function setSellingFrom(ProductData $productData, string $date): void
    {
        $productData->sellingFrom = new DateTime($date);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $date
     */
    public function setSellingTo(ProductData $productData, string $date): void
    {
        $productData->sellingTo = new DateTime($date);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param array $parametersValues
     */
    public function setProductParameterValues(ProductData $productData, array $parametersValues): void
    {
        foreach ($parametersValues as $parameterValues) {
            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $parameterValues['parameter'];

            foreach ($parameterValues['values'] as $locale => $parameterValue) {
                $productParameterValueData = $this->productParameterValueDataFactory->create();

                $parameterValueData = $this->parameterValueDataFactory->create();
                $parameterValueData->text = $parameterValue;
                $parameterValueData->locale = $locale;

                if ($parameter->isSlider()) {
                    $parameterValueData->numericValue = $parameterValue;
                }

                $productParameterValueData->parameterValueData = $parameterValueData;
                $productParameterValueData->parameter = $parameter;

                $productData->parameters[] = $productParameterValueData;
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $price
     */
    public function setPriceForAllPricingGroups(ProductData $productData, string $price): void
    {
        foreach ($this->pricingGroupFacade->getAll() as $pricingGroup) {
            $vat = $this->persistentReferenceFacade->getReferenceForDomain(VatDataFixture::VAT_HIGH, $pricingGroup->getDomainId(), Vat::class);
            $currencyCzk = $this->persistentReferenceFacade->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

            $money = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
                Money::create($price),
                $currencyCzk,
                $vat->getPercent(),
                $pricingGroup->getDomainId(),
            );

            $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = $money;
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string[] $categoryReferences
     */
    public function setCategoriesForAllDomains(ProductData $productData, array $categoryReferences): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($categoryReferences as $categoryReference) {
                $productData->categoriesByDomainId[$domainId][] = $this->persistentReferenceFacade->getReference($categoryReference, Category::class);
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param string $brandReference
     */
    public function setBrand(ProductData $productData, string $brandReference): void
    {
        $productData->brand = $this->persistentReferenceFacade->getReference($brandReference, Brand::class);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param int $quantity
     */
    public function setStocksQuantity(ProductData $productData, int $quantity)
    {
        $stocks = $this->stockRepository->getAllStocks();

        foreach ($stocks as $stock) {
            $productStockData = $this->productStockDataFactory->createFromStock($stock);
            $productStockData->productQuantity = $quantity;
            $productData->productStockData[$stock->getId()] = $productStockData;
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param int $orderingPriority
     */
    public function setOrderingPriority(ProductData $productData, int $orderingPriority): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->orderingPriorityByDomainId[$domainId] = $orderingPriority;
        }
    }
}
