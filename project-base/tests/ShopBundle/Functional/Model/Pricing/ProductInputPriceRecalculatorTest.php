<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\ShopBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\ShopBundle\Model\Product\Product;
use Shopsys\ShopBundle\Model\Product\ProductData;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductInputPriceRecalculatorTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     * @inject
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     * @inject
     */
    private $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceRecalculator
     * @inject
     */
    private $productInputPriceRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithoutVat()
    {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $productData = $this->productDataFactory->create();
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $this->setVats($productData);
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, Money::create(1000));
        $inputPriceType = $this->pricingSetting->getInputPriceType();
        $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent($productManualInputPrice, $inputPriceType, '15');

        $this->assertThat($productManualInputPrice->getInputPrice(), new IsMoneyEqual(Money::create('1052.173913')));
    }

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithVat()
    {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $productData = $this->productDataFactory->create();
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $this->setVats($productData);
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, Money::create(1000));

        $inputPriceType = $this->pricingSetting->getInputPriceType();
        $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent($productManualInputPrice, $inputPriceType, '15');

        $this->assertThat($productManualInputPrice->getInputPrice(), new IsMoneyEqual(Money::create(1000)));
    }

    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatFormDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
