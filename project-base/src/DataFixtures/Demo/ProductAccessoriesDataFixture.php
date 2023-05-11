<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\ProductDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductAccessoriesDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private ProductDataFactory $productDataFactory;

    private ProductFacade $productFacade;

    /**
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        ProductDataFactoryInterface $productDataFactory,
        ProductFacade $productFacade
    ) {
        $this->productDataFactory = $productDataFactory;
        $this->productFacade = $productFacade;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $productData = $this->productDataFactory->createFromProduct($product);
        /** @var \App\Model\Product\Product $product24 */
        $product24 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '24');
        /** @var \App\Model\Product\Product $product13 */
        $product13 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '13');
        $productData->accessories = [
            $product24,
            $product13,
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
