<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class RelatedProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        private ProductDataFactory $productDataFactory,
        private ProductFacade $productFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $products = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6', Product::class),
        ];

        foreach ($products as $key => $product) {
            $productData = $this->productDataFactory->createFromProduct($product);
            $relatedProducts = $products;
            unset($relatedProducts[$key]);
            $productData->relatedProducts = $relatedProducts;
            $this->productFacade->edit($product->getId(), $productData);
        }
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
