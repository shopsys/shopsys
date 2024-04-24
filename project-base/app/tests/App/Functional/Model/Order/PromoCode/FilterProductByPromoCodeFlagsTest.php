<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\PromoCode;

use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeDataFactory;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Product\Flag\Flag;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory;
use Tests\App\Test\TransactionFunctionalTestCase;

class FilterProductByPromoCodeFlagsTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductPromoCodeFiller $productPromoCodeFiller;

    /**
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    /**
     * @inject
     */
    private PromoCodeFlagFactory $promoCodeFlagFactory;

    /**
     * @inject
     */
    private PromoCodeLimitFactory $promoCodeLimitFactory;

    public function testProductIsIncludedWhenFlagIsInclusive(): void
    {
        $productWithActionFlag = $this->getProductWithActionFlag();

        $promoCodeFlag = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_ACTION),
            PromoCodeFlag::TYPE_INCLUSIVE,
        );
        $promoCode = $this->createPromoCodeWithFlags([$promoCodeFlag]);

        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithActionFlag, $promoCode);
        self::assertEquals($productWithActionFlag, $result);
    }

    public function testProductIsIncludedWhenFlagIsExclusive(): void
    {
        $productWithoutFlag = $this->getProductWithoutFlag();

        $promoCodeFlag = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_NEW),
            PromoCodeFlag::TYPE_EXCLUSIVE,
        );
        $promoCode = $this->createPromoCodeWithFlags([$promoCodeFlag]);

        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithoutFlag, $promoCode);
        self::assertEquals($productWithoutFlag, $result);
    }

    public function testProductIsIncludedWhenMultipleFlagsSet(): void
    {
        $productWithNewAndMadeInCzFlags = $this->getProductWithNewAndMadeInCzFlags();

        $promoCodeFlagNew = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_NEW),
            PromoCodeFlag::TYPE_INCLUSIVE,
        );

        $promoCodeFlagMadeInCz = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ),
            PromoCodeFlag::TYPE_INCLUSIVE,
        );

        $promoCodeFlagAction = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_ACTION),
            PromoCodeFlag::TYPE_EXCLUSIVE,
        );

        $promoCode = $this->createPromoCodeWithFlags([$promoCodeFlagNew, $promoCodeFlagMadeInCz, $promoCodeFlagAction]);

        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithNewAndMadeInCzFlags, $promoCode);
        self::assertEquals($productWithNewAndMadeInCzFlags, $result);
    }

    public function testProductIsIncludedWhenNoPromoCodeFlagSet(): void
    {
        $promoCode = $this->createPromoCodeWithFlags([]);

        $productWithoutFlag = $this->getProductWithoutFlag();
        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithoutFlag, $promoCode);
        self::assertEquals($productWithoutFlag, $result);

        $productWithActionFlag = $this->getProductWithActionFlag();
        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithActionFlag, $promoCode);
        self::assertEquals($productWithActionFlag, $result);
    }

    public function testProductIsNotIncludedWhenFlagIsInclusive(): void
    {
        $productWithoutFlags = $this->getProductWithoutFlag();

        $promoCodeFlag = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_ACTION),
            PromoCodeFlag::TYPE_INCLUSIVE,
        );
        $promoCode = $this->createPromoCodeWithFlags([$promoCodeFlag]);

        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithoutFlags, $promoCode);
        self::assertNull($result);
    }

    public function testProductIsNotIncludedWhenFlagIsExclusive(): void
    {
        $productWithActionFlag = $this->getProductWithActionFlag();

        $promoCodeFlag = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_ACTION),
            PromoCodeFlag::TYPE_EXCLUSIVE,
        );
        $promoCode = $this->createPromoCodeWithFlags([$promoCodeFlag]);

        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithActionFlag, $promoCode);
        self::assertNull($result);
    }

    public function testProductIsNotIncludedWhenSomeFlagDontApply(): void
    {
        $productWithActionFlag = $this->getProductWithActionFlag();

        $promoCodeFlagAction = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_ACTION),
            PromoCodeFlag::TYPE_INCLUSIVE,
        );

        $promoCodeFlagMadeInCz = $this->promoCodeFlagFactory->create(
            $this->getFlag(FlagDataFixture::FLAG_PRODUCT_MADEIN_CZ),
            PromoCodeFlag::TYPE_INCLUSIVE,
        );

        $promoCode = $this->createPromoCodeWithFlags([$promoCodeFlagMadeInCz, $promoCodeFlagAction]);

        $result = $this->productPromoCodeFiller->filterProductByPromoCodeFlags($productWithActionFlag, $promoCode);
        self::assertNull($result);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $promoCodeFlags
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    private function createPromoCodeWithFlags(array $promoCodeFlags): PromoCode
    {
        $promoCodeLimit = $this->promoCodeLimitFactory->create('1', '10');

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->flags = $promoCodeFlags;
        $promoCodeData->domainId = $this->domain->getId();
        $promoCodeData->code = 'present';
        $promoCodeData->discountType = PromoCode::DISCOUNT_TYPE_NOMINAL;
        $promoCodeData->limits = [$promoCodeLimit];

        return $this->promoCodeFacade->create($promoCodeData);
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function getProductWithActionFlag(): Product
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function getProductWithoutFlag(): Product
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 3, Product::class);
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function getProductWithNewAndMadeInCzFlags(): Product
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 19, Product::class);
    }

    /**
     * @param string $referenceName
     * @return \App\Model\Product\Flag\Flag
     */
    private function getFlag(string $referenceName): Flag
    {
        return $this->getReference($referenceName, Flag::class);
    }
}
