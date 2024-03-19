<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\Component\Image\Image;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\Unit\Unit;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
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
    private function getDefaultProductData()
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $names = [];

        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'Name';
        }
        $productData->name = $names;
        $productData->categoriesByDomainId = [Domain::FIRST_DOMAIN_ID => [$category]];
        $productData->catnum = '123';
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES, Unit::class);
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
    private function setPriceForAllDomains(ProductData $productData, ?Money $price)
    {
        $manualInputPrices = [];

        foreach ($this->pricingGroupFacade->getAll() as $pricingGroup) {
            $manualInputPrices[$pricingGroup->getId()] = $price;
        }

        $productData->manualInputPricesByPricingGroupId = $manualInputPrices;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility
     */
    private function createProductAndGetVisibility(ProductData $productData): ProductVisibility
    {
        $product = $this->productFacade->create($productData);

        $id = $product->getId();
        $this->createImage('product', $id);

        $this->em->clear();

        $this->handleDispatchedRecalculationMessages();

        /** @var \App\Model\Product\Product $productAgain */
        $productAgain = $this->em->getRepository(Product::class)->find($id);

        return $this->getVisibilityForProduct($productAgain);
    }

    /**
     * @param \App\Model\Product\Product $productAgain
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility
     */
    private function getVisibilityForProduct(Product $productAgain): ProductVisibility
    {
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        return $this->em->getRepository(ProductVisibility::class)->findOneBy([
            'product' => $productAgain,
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => Domain::FIRST_DOMAIN_ID,
        ]);
    }

    public function testIsVisibleOnAnyDomainWhenHidden(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->hidden = true;

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleOnFirstDomainWhenHidden(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->hidden = false;
        $productData->domainHidden[Domain::FIRST_DOMAIN_ID] = true;

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenNotHidden(): void
    {
        $productData = $this->getDefaultProductData();

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingInFuture(): void
    {
        $sellingFrom = new DateTime('now');
        $sellingFrom->modify('+1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingFrom = $sellingFrom;

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleOnAnyDomainWhenSellingInPast(): void
    {
        $sellingTo = new DateTime('now');
        $sellingTo->modify('-1 day');

        $productData = $this->getDefaultProductData();
        $productData->sellingTo = $sellingTo;

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
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

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleWhenZeroPrice(): void
    {
        $productData = $this->getDefaultProductData();
        $this->setPriceForAllDomains($productData, Money::zero());

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleWithFilledName(): void
    {
        $productData = $this->getDefaultProductData();

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleWithEmptyName(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->name = ['cs' => null, 'en' => null];

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testIsVisibleInVisibileCategory(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TOYS, Category::class);

        $productData = $this->getDefaultProductData();
        $productData->categoriesByDomainId = [Domain::FIRST_DOMAIN_ID => [$category]];

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertTrue($productVisibility->isVisible());
    }

    public function testIsNotVisibleInHiddenCategory(): void
    {
        $productData = $this->getDefaultProductData();
        $productData->categoriesByDomainId = [];

        $productVisibility = $this->createProductAndGetVisibility($productData);

        $this->assertFalse($productVisibility->isVisible());
    }

    public function testRefreshProductsVisibilityVisibleVariants(): void
    {
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53', Product::class);
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54', Product::class);
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69', Product::class);

        $variant1productData = $this->productDataFactory->createFromProduct($variant1);
        $variant1productData->hidden = true;
        $this->productFacade->edit($variant1->getId(), $variant1productData);

        $this->productVisibilityRepository->refreshProductsVisibility([53, 54, 69]);

        $this->assertFalse($this->getVisibilityForProduct($variant1)->isVisible());
        $this->assertTrue($this->getVisibilityForProduct($variant2)->isVisible());
        $this->assertTrue($this->getVisibilityForProduct($mainVariant)->isVisible());
    }

    public function testRefreshProductsVisibilityNotVisibleVariants(): void
    {
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53', Product::class);
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54', Product::class);
        $variant3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148', Product::class);
        $variant4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '149', Product::class);
        $variant5 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '150', Product::class);
        $variant6 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '151', Product::class);
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69', Product::class);

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

        $this->productVisibilityRepository->refreshProductsVisibility([53, 54, 148, 149, 150, 151, 69]);

        $this->assertFalse($this->getVisibilityForProduct($variant1)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($variant2)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($variant3)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($variant4)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($variant5)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($variant6)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($mainVariant)->isVisible());
    }

    public function testRefreshProductsVisibilityNotVisibleMainVariant(): void
    {
        $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53', Product::class);
        $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54', Product::class);
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69', Product::class);

        $mainVariantProductData = $this->productDataFactory->createFromProduct($mainVariant);
        $mainVariantProductData->hidden = true;
        $this->productFacade->edit($mainVariant->getId(), $mainVariantProductData);

        $this->productVisibilityRepository->refreshProductsVisibility([53, 54, 69]);

        $this->assertFalse($this->getVisibilityForProduct($variant1)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($variant2)->isVisible());
        $this->assertFalse($this->getVisibilityForProduct($mainVariant)->isVisible());
    }
}
