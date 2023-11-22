<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

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
    public function load(ObjectManager $manager): void
    {
        /** @var \App\Model\Product\Product[] $products */
        $products = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6'),
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
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
