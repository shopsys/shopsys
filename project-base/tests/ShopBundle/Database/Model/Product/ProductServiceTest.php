<?php

namespace Tests\ShopBundle\Database\Model\Product;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\DataFixtures\Base\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Base\UnitDataFixture;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductService;
use Tests\ShopBundle\Test\DatabaseTestCase;

class ProductServiceTest extends DatabaseTestCase
{
    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithoutVat()
    {
        $em = $this->getEntityManager();

        $productService = $this->getContainer()->get(ProductService::class);
        /* @var $productService \Shopsys\FrameworkBundle\Model\Product\ProductService */
        $setting = $this->getContainer()->get(Setting::class);
        /* @var $setting \Shopsys\FrameworkBundle\Component\Setting\Setting */

        $setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);

        $vatData = new VatData('vat', 21);
        $vat = new Vat($vatData);
        $availability = new Availability(new AvailabilityData([], 0));

        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        $productData = new ProductData();
        $productData->price = 1000;
        $productData->vat = $vat;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->availability = $availability;
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, 1000);

        $em->persist($vat);
        $em->persist($availability);
        $em->persist($product);
        $em->flush();

        $productService->recalculateInputPriceForNewVatPercent($product, [$productManualInputPrice], 15);

        $this->assertSame('1052.173913', (string)$product->getPrice());
        $this->assertSame('1052.173913', (string)$productManualInputPrice->getInputPrice());
    }

    public function testRecalculateInputPriceForNewVatPercentWithInputPriceWithVat()
    {
        $em = $this->getEntityManager();

        $productService = $this->getContainer()->get(ProductService::class);
        /* @var $productService \Shopsys\FrameworkBundle\Model\Product\ProductService */
        $setting = $this->getContainer()->get(Setting::class);
        /* @var $setting \Shopsys\FrameworkBundle\Component\Setting\Setting */

        $setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);

        $vatData = new VatData('vat', 21);
        $vat = new Vat($vatData);
        $availability = new Availability(new AvailabilityData([], 0));

        $pricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);

        $productData = new ProductData();
        $productData->price = 1000;
        $productData->vat = $vat;
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->availability = $availability;
        $product = Product::create($productData);

        $productManualInputPrice = new ProductManualInputPrice($product, $pricingGroup, 1000);

        $em->persist($vat);
        $em->persist($availability);
        $em->persist($product);
        $em->flush();

        $productService->recalculateInputPriceForNewVatPercent($product, [$productManualInputPrice], 15);

        $this->assertSame('1000', (string)$product->getPrice());
        $this->assertSame('1000', (string)$productManualInputPrice->getInputPrice());
    }
}
