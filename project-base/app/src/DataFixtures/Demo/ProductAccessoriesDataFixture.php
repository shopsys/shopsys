<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\ProductDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductAccessoriesDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        private readonly ProductDataFactory $productDataFactory,
        private readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $productData = $this->productDataFactory->createFromProduct($product);

        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2');
        /** @var \App\Model\Product\Product $product3 */
        $product3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3');
        /** @var \App\Model\Product\Product $product4 */
        $product4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4');
        /** @var \App\Model\Product\Product $product5 */
        $product5 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5');
        /** @var \App\Model\Product\Product $product6 */
        $product6 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6');
        /** @var \App\Model\Product\Product $product7 */
        $product7 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7');
        /** @var \App\Model\Product\Product $product8 */
        $product8 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '8');
        /** @var \App\Model\Product\Product $product9 */
        $product9 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '9');
        /** @var \App\Model\Product\Product $productSoldOut */
        $productSoldOut = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '10');
        /** @var \App\Model\Product\Product $product11 */
        $product11 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '11');
        /** @var \App\Model\Product\Product $product13 */
        $product13 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '13');
        /** @var \App\Model\Product\Product $product24 */
        $product24 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '24');
        /** @var \App\Model\Product\Product $productMainVariant */
        $productMainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');
        /** @var \App\Model\Product\Product $productExcludedFromSale */
        $productExcludedFromSale = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '76');
        /** @var \App\Model\Product\Product $productVariant */
        $productVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148');

        $productData->accessories = [
            $product2,
            $product3,
            $productMainVariant,
            $product4,
            $product5,
            $product6,
            $product7,
            $product8,
            $product9,
            $product11,
            $product24,
            $product13,
            $productExcludedFromSale,
            $productVariant,
            $productSoldOut,
        ];
        $this->productFacade->edit($product->getId(), $productData);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
