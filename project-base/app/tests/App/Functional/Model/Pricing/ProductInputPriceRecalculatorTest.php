<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class ProductInputPriceRecalculatorTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private Setting $setting;

    /**
     * @inject
     */
    private PricingSetting $pricingSetting;

    /**
     * @inject
     */
    private ProductInputPriceRecalculator $productInputPriceRecalculator;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithoutVat()
    {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        $productData = $this->productDataFactory->create();
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->manualInputPricesByPricingGroupId = [
            1 => Money::zero(),
            2 => Money::zero(),
        ];
        $this->setVats($productData);
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, Money::create(1000));
        $inputPriceType = $this->pricingSetting->getInputPriceType();
        $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent(
            $productManualInputPrice,
            $inputPriceType,
            '15',
        );

        $this->assertThat($productManualInputPrice->getInputPrice(), new IsMoneyEqual(Money::create('1052.173913')));
    }

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithVat()
    {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        $productData = $this->productDataFactory->create();
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->manualInputPricesByPricingGroupId = [
            1 => Money::zero(),
            2 => Money::zero(),
        ];
        $this->setVats($productData);
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, Money::create(1000));

        $inputPriceType = $this->pricingSetting->getInputPriceType();
        $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent(
            $productManualInputPrice,
            $inputPriceType,
            '15',
        );

        $this->assertThat($productManualInputPrice->getInputPrice(), new IsMoneyEqual(Money::create(1000)));
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
