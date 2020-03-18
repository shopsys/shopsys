<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductVisibilityRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     * @inject
     */
    private $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator
     * @inject
     */
    private $productPriceRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository
     * @inject
     */
    private $productVisibilityRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     * @inject
     */
    private $localization;

    /**
     * @return \App\Model\Product\ProductData
     */
    private function getDefaultProductData()
    {
        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'Name';
        }
        $productData->name = $names;
        $productData->categoriesByDomainId = [Domain::FIRST_DOMAIN_ID => [$category]];
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $this->setPriceForAllDomains($productData, Money::create(100));
        $this->setVatsForAllDomains($productData);

        return $productData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    private function setVatsForAllDomains(ProductData $productData): void
    {
        $productVats = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $vatData = new VatData();
            $vatData->name = 'vat';
            $vatData->percent = '21';
            $vat = new Vat($vatData, $domainId);
            $this->em->persist($vat);

            $productVats[$domainId] = $vat;
        }

        $productData->vatsIndexedByDomainId = $productVats;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $price
     */
    private function setPriceForAllDomains(ProductData $productData, ?Money $price)
    {
        $manualInputPrices = [];

        foreach ($this->pricingGroupFacade->getAll() as $pricingGroup) {
            $manualInputPrices[$pricingGroup->getId()] = $price;
        }

        $productData->manualInputPricesByPricingGroupId = $manualInputPrices;
    }

    public function testIsVisibleOnAnyDomainWhenHidden()
    {
        $productData = $this->getDefaultProductData();
        $productData->hidden = true;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility1 */
        $productVisibility1 = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $productAgain,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productAgain->isVisible());
        $this->assertFalse($productVisibility1->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenNotHidden()
    {
        $productData = $this->getDefaultProductData();
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility1 */
        $productVisibility1 = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $productAgain->getId(),
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertTrue($productAgain->isVisible());
        $this->assertTrue($productVisibility1->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingInFuture()
    {
        $sellingFrom = new DateTime('now');
        $sellingFrom->modify('+1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingFrom = $sellingFrom;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        $this->assertFalse($productAgain->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingInPast()
    {
        $sellingTo = new DateTime('now');
        $sellingTo->modify('-1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingTo = $sellingTo;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        $this->assertFalse($productAgain->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingNow()
    {
        $sellingFrom = new DateTime('now');
        $sellingFrom->modify('-1 day');
        $sellingTo = new DateTime('now');
        $sellingTo->modify('+1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingFrom = $sellingFrom;
        $productData->sellingTo = $sellingTo;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        $this->assertTrue($productAgain->isVisible());
    }

    public function testIsNotVisibleWhenZeroOrNullPrice()
    {
        $productData = $this->getDefaultProductData();
        $this->setPriceForAllDomains($productData, Money::zero());
        $product1 = $this->productFacade->create($productData);

        $this->setPriceForAllDomains($productData, null);
        $product2 = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $product1Id = $product1->getId();
        $product2Id = $product2->getId();
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $product1Again */
        $product1Again = $this->em->getRepository(Product::class)->find($product1Id);
        /** @var \App\Model\Product\Product $product2Again */
        $product2Again = $this->em->getRepository(Product::class)->find($product2Id);

        $this->assertFalse($product1Again->isVisible());
        $this->assertFalse($product2Again->isVisible());
    }

    public function testIsVisibleWithFilledName()
    {
        $productData = $this->getDefaultProductData();
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleWithEmptyName()
    {
        $productData = $this->getDefaultProductData();
        $productData->name = ['cs' => null, 'en' => null];
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleInVisibileCategory()
    {
        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TOYS);

        $productData = $this->getDefaultProductData();
        $productData->categoriesByDomainId = [Domain::FIRST_DOMAIN_ID => [$category]];
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleInHiddenCategory()
    {
        $productData = $this->getDefaultProductData();
        $productData->categoriesByDomainId = [];
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsNotVisibleWhenZeroManualPrice()
    {
        $productData = $this->getDefaultProductData();
        $this->setPriceForAllDomains($productData, Money::create(10));

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        $pricingGroupWithZeroPriceId = $pricingGroup->getId();

        $productData->manualInputPricesByPricingGroupId[$pricingGroupWithZeroPriceId] = Money::zero();

        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroupWithZeroPriceId,
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsNotVisibleWhenNullManualPrice()
    {
        $productData = $this->getDefaultProductData();

        $allPricingGroups = $this->pricingGroupFacade->getAll();
        foreach ($allPricingGroups as $pricingGroup) {
            $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = Money::create(10);
        }

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        $pricingGroupWithNullPriceId = $pricingGroup->getId();
        $productData->manualInputPricesByPricingGroupId[$pricingGroupWithNullPriceId] = null;

        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroupWithNullPriceId,
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testRefreshProductsVisibilityVisibleVariants()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->hidden = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        $this->productVisibilityRepository->refreshProductsVisibility(true);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($variant3);
        $this->em->refresh($mainVariant);

        $this->assertFalse($variant1->isVisible());
        $this->assertTrue($variant2->isVisible());
        $this->assertTrue($variant3->isVisible());
        $this->assertTrue($mainVariant->isVisible());
    }

    public function testRefreshProductsVisibilityNotVisibleVariants()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->hidden = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        $variant2productData = $this->productDataFactory->createFromProduct($variant2);
        $variant2productData->hidden = true;
        $this->productFacade->edit($variant2->getId(), $variant2productData);

        $variant3productData = $this->productDataFactory->createFromProduct($variant3);
        $variant3productData->hidden = true;
        $this->productFacade->edit($variant3->getId(), $variant3productData);

        $this->productVisibilityRepository->refreshProductsVisibility(true);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($variant3);
        $this->em->refresh($mainVariant);

        $this->assertFalse($variant1->isVisible());
        $this->assertFalse($variant2->isVisible());
        $this->assertFalse($variant3->isVisible());
        $this->assertFalse($mainVariant->isVisible());
    }

    public function testRefreshProductsVisibilityNotVisibleMainVariant()
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $mainVariantproductData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantproductData->hidden = true;
        $this->productFacade->edit($mainVariant->getId(), $mainVariantproductData);

        $this->productVisibilityRepository->refreshProductsVisibility(true);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($variant3);
        $this->em->refresh($mainVariant);

        $this->assertFalse($variant1->isVisible());
        $this->assertFalse($variant2->isVisible());
        $this->assertFalse($variant3->isVisible());
        $this->assertFalse($mainVariant->isVisible());
    }
}
