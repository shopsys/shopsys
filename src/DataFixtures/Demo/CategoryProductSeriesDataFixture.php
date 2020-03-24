<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class CategoryProductSeriesDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade
     */
    protected $categoryProductSeriesFacade;

    /**
     * @param \App\Model\Category\CategoryProductSeries\CategoryProductSeriesFacade $categoryProductSeriesFacade
     */
    public function __construct(CategoryProductSeriesFacade $categoryProductSeriesFacade)
    {
        $this->categoryProductSeriesFacade = $categoryProductSeriesFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $productSeries = [
            $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_DANIELA),
            $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_KARIN),
            $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_CIRRI),
            $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_GERALT),
            $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_YENNEFER),
            $this->getReference(ProductSeriesDataFixture::PRODUCT_SERIES_TISIA),
        ];

        $categories = [
            $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            $this->getReference(CategoryDataFixture::CATEGORY_TV),
        ];
        foreach ($categories as $key => $category) {
            $this->categoryProductSeriesFacade->saveProductSeriesForCategory($category, $productSeries);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            CategoryDataFixture::class,
        ];
    }
}
