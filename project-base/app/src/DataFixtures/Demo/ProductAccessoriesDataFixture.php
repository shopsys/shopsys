<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Product;
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
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $productData = $this->productDataFactory->createFromProduct($product);

        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class);
        $product3 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3', Product::class);
        $product4 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4', Product::class);
        $product5 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);
        $product6 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6', Product::class);
        $product7 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '7', Product::class);
        $product8 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '8', Product::class);
        $product9 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '9', Product::class);
        $productSoldOut = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '10', Product::class);
        $product11 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '11', Product::class);
        $product13 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '13', Product::class);
        $product24 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '24', Product::class);
        $productMainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69', Product::class);
        $productExcludedFromSale = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '76', Product::class);
        $productVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148', Product::class);

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
