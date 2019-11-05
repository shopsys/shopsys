<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

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
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithoutVat()
    {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        $vat = new Vat($vatData);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $productData = $this->productDataFactory->create();
        $productData->vat = $vat;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, Money::create(1000));
        $inputPriceType = $this->pricingSetting->getInputPriceType();
        $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent($productManualInputPrice, $inputPriceType, '15');

        $this->assertThat($productManualInputPrice->getInputPrice(), new IsMoneyEqual(Money::create('1052.173913')));
    }

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithVat()
    {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = '21';
        $vat = new Vat($vatData);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $productData = $this->productDataFactory->create();
        $productData->vat = $vat;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, Money::create(1000));

        $inputPriceType = $this->pricingSetting->getInputPriceType();
        $this->productInputPriceRecalculator->recalculateInputPriceForNewVatPercent($productManualInputPrice, $inputPriceType, '15');

        $this->assertThat($productManualInputPrice->getInputPrice(), new IsMoneyEqual(Money::create(1000)));
    }
}
