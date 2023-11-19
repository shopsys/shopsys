<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\Component\Image\Image;
use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductVisibilityRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private PricingGroupFacade $pricingGroupFacade;

    /**
     * @inject
     */
    private ProductPriceRecalculator $productPriceRecalculator;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductVisibilityRepository $productVisibilityRepository;

    /**
     * @inject
     */
    private Localization $localization;

    /**
     * @return \App\Model\Product\ProductData
     */
    private function getDefaultProductData(): \App\Model\Product\ProductData
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
        $productData->catnum = '123';
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $this->setPriceForAllDomains($productData, Money::create(100));
        $this->setVatsForAllDomains($productData);
        $this->setDescriptionForAllDomain($productData);

        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setDescriptionForAllDomain(ProductData $productData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            if (!array_key_exists($domainId, $productData->descriptions) || $productData->descriptions[$domainId] === null || $productData->descriptions[$domainId] === '') {
                $productData->descriptions[$domainId] = 'description';
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
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
    private function setPriceForAllDomains(ProductData $productData, ?Money $price): void
    {
        $manualInputPrices = [];

        foreach ($this->pricingGroupFacade->getAll() as $pricingGroup) {
            $manualInputPrices[$pricingGroup->getId()] = $price;
        }

        $productData->manualInputPricesByPricingGroupId = $manualInputPrices;
    }

    public function testIsVisibleOnAnyDomainWhenHidden(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->hidden = true;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->createImage('product', $id);
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility1 */
        $productVisibility1 = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $productAgain,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productAgain->isVisible());
        $this->assertFalse($productVisibility1->isVisible());
    }

    public function testIsVisibleOnFirstDomainWhenHidden(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->hidden = false;
        $productData->domainHidden[Domain::FIRST_DOMAIN_ID] = true;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->createImage('product', $id);
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

    public function testIsVisibleOnAnyDomainWhenNotHidden(): void
    {
        $productData = $this->getDefaultProductData();
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->createImage('product', $id);
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility1 */
        $productVisibility1 = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $productAgain->getId(),
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertTrue($productAgain->isVisible());
        $this->assertTrue($productVisibility1->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingInFuture(): void
    {
        $sellingFrom = new DateTime('now');
        $sellingFrom->modify('+1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingFrom = $sellingFrom;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->createImage('product', $id);
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        $this->assertFalse($productAgain->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingInPast(): void
    {
        $sellingTo = new DateTime('now');
        $sellingTo->modify('-1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingTo = $sellingTo;
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();

        $this->em->flush();
        $id = $product->getId();
        $this->createImage('product', $id);
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        $this->assertFalse($productAgain->isVisible());
    }

    /**
     * @param string $entityName
     * @param int $entityId
     */
    private function createImage(string $entityName, int $entityId): void
    {
        $namesIndexedByLocale = [];

        foreach ($this->domain->getAllLocales() as $locale) {
            $namesIndexedByLocale[$locale] = $entityName . '-' . $entityId . ' (' . $locale . ')';
        }

        $image = new Image($entityName, $entityId, $namesIndexedByLocale, null, null);
        $image->setAkeneoImageType('image_main');
        $image->setFileAsUploaded('image', '/web/public/frontend/images/noimage.png');
        $this->em->persist($image);
        $this->em->flush();
    }

    public function testIsVisibleOnAnyDomainWhenSellingNow(): void
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
        $this->createImage('product', $id);
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        $this->assertTrue($productAgain->isVisible());
    }

    public function testIsNotVisibleWhenZeroPrice(): void
    {
        $productData = $this->getDefaultProductData();
        $this->setPriceForAllDomains($productData, Money::zero());
        $product1 = $this->productFacade->create($productData);

        $this->productPriceRecalculator->runImmediateRecalculations();

        $product1Id = $product1->getId();
        $this->createImage('product', $product1Id);
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \App\Model\Product\Product $product1Again */
        $product1Again = $this->em->getRepository(Product::class)->find($product1Id);

        $this->assertFalse($product1Again->isVisible());
    }

    public function testIsVisibleWithFilledName(): void
    {
        $productData = $this->getDefaultProductData();
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();
        $this->createImage('product', $product->getId());
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleWithEmptyName(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->name = ['cs' => null, 'en' => null];
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();
        $this->createImage('product', $product->getId());
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleInVisibileCategory(): void
    {
        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TOYS);

        $productData = $this->getDefaultProductData();
        $productData->categoriesByDomainId = [Domain::FIRST_DOMAIN_ID => [$category]];
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();
        $this->createImage('product', $product->getId());
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibility $productVisibility */
        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleInHiddenCategory(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->categoriesByDomainId = [];
        $product = $this->productFacade->create($productData);
        $this->productPriceRecalculator->runImmediateRecalculations();
        $this->createImage('product', $product->getId());
        $this->em->clear();

        $this->productVisibilityRepository->refreshProductsVisibility();

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );

        $productVisibility = $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testRefreshProductsVisibilityVisibleVariants(): void
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->hidden = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        $this->productVisibilityRepository->refreshProductsVisibility(true);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($mainVariant);

        $this->assertFalse($variant1->isVisible());
        $this->assertTrue($variant2->isVisible());
        $this->assertTrue($mainVariant->isVisible());
    }

    public function testRefreshProductsVisibilityNotVisibleVariants(): void
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $variant3 */
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');
        /** @var \App\Model\Product\Product $variant4 */
        $variant4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '149');
        /** @var \App\Model\Product\Product $variant5 */
        $variant5 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '150');
        /** @var \App\Model\Product\Product $variant6 */
        $variant6 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '151');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->hidden = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        $variant2productData = $this->productDataFactory->createFromProduct($variant2);
        $variant2productData->hidden = true;
        $this->productFacade->edit($variant2->getId(), $variant2productData);

        $variant3productData = $this->productDataFactory->createFromProduct($variant3);
        $variant3productData->hidden = true;
        $this->productFacade->edit($variant3->getId(), $variant3productData);

        $variant4productData = $this->productDataFactory->createFromProduct($variant4);
        $variant4productData->hidden = true;
        $this->productFacade->edit($variant4->getId(), $variant4productData);

        $variant5productData = $this->productDataFactory->createFromProduct($variant5);
        $variant5productData->hidden = true;
        $this->productFacade->edit($variant5->getId(), $variant5productData);

        $variant6productData = $this->productDataFactory->createFromProduct($variant6);
        $variant6productData->hidden = true;
        $this->productFacade->edit($variant6->getId(), $variant6productData);

        $this->productVisibilityRepository->refreshProductsVisibility(true);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($variant3);
        $this->em->refresh($variant4);
        $this->em->refresh($variant5);
        $this->em->refresh($variant6);
        $this->em->refresh($mainVariant);

        $this->assertFalse($variant1->isVisible());
        $this->assertFalse($variant2->isVisible());
        $this->assertFalse($variant3->isVisible());
        $this->assertFalse($variant4->isVisible());
        $this->assertFalse($variant5->isVisible());
        $this->assertFalse($variant6->isVisible());
        $this->assertFalse($mainVariant->isVisible());
    }

    public function testRefreshProductsVisibilityNotVisibleMainVariant(): void
    {
        /** @var \App\Model\Product\Product $variant1 */
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
        /** @var \App\Model\Product\Product $variant2 */
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
        /** @var \App\Model\Product\Product $mainVariant */
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');

        $mainVariantproductData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantproductData->hidden = true;
        $this->productFacade->edit($mainVariant->getId(), $mainVariantproductData);

        $this->productVisibilityRepository->refreshProductsVisibility(true);

        $this->em->refresh($variant1);
        $this->em->refresh($variant2);
        $this->em->refresh($mainVariant);

        $this->assertFalse($variant1->isVisible());
        $this->assertFalse($variant2->isVisible());
        $this->assertFalse($mainVariant->isVisible());
    }
}
