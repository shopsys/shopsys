<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Series\ProductSeriesProductFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class ProductSeriesProductDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesProductFacade
     */
    private $productSeriesProductFacade;

    /**
     * @param \App\Model\Product\Series\ProductSeriesProductFacade $productSeriesProductFacade
     */
    public function __construct(ProductSeriesProductFacade $productSeriesProductFacade)
    {
        $this->productSeriesProductFacade = $productSeriesProductFacade;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Product\Series\ProductSeries $productSeries1 */
        $productSeries1 = $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_YENNEFER);
        /** @var \App\Model\Product\Series\ProductSeries $productSeries2 */
        $productSeries2 = $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_DANIELA);
        /** @var \App\Model\Product\Series\ProductSeries $productSeries3 */
        $productSeries3 = $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_GERALT);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $this->productSeriesProductFacade->editProductSeriesProductRelation(
            $product,
            [
                $productSeries1->getAkeneoCode(),
                $productSeries2->getAkeneoCode(),
                $productSeries3->getAkeneoCode(),
            ]
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2');
        $this->productSeriesProductFacade->editProductSeriesProductRelation(
            $product,
            [
                $productSeries1->getAkeneoCode(),
                $productSeries2->getAkeneoCode(),
                $productSeries3->getAkeneoCode(),
            ]
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3');
        $this->productSeriesProductFacade->editProductSeriesProductRelation(
            $product,
            [
                $productSeries1->getAkeneoCode(),
                $productSeries2->getAkeneoCode(),
            ]
        );

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '4');
        $this->productSeriesProductFacade->editProductSeriesProductRelation(
            $product,
            [
                $productSeries1->getAkeneoCode(),
            ]
        );

        for ($i = 5; $i < 20; $i++) {
            $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $i);
            $this->productSeriesProductFacade->editProductSeriesProductRelation(
                $product,
                [
                    $productSeries1->getAkeneoCode(),
                ]
            );
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            ProductDataFixture::class,
            ProductSeriesDataFixture::class,
        ];
    }
}
