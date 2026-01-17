<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteDataFactory;
use Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteFacade;

class AutocompleteFavoriteDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly AutocompleteFavoriteDataFactory $autocompleteFavoriteDataFactory,
        private readonly AutocompleteFavoriteFacade $autocompleteFavoriteFacade,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $this->loadForDomain($domainConfig->getId());
        }
    }

    private function loadForDomain(int $domainId): void
    {
        $autocompleteFavoriteData = $this->autocompleteFavoriteDataFactory->createInstance();

        $autocompleteFavoriteData->products = $this->getProductsForDomain();
        $autocompleteFavoriteData->categories = $this->getCategoriesForDomain();
        $autocompleteFavoriteData->brands = $this->getBrandsForDomain();

        $this->autocompleteFavoriteFacade->saveAllForDomain($domainId, $autocompleteFavoriteData);
    }

    /**
     * @return \App\Model\Product\Product[]
     */
    private function getProductsForDomain(): array
    {
        $productsReferencesForDomain = [
            ProductDataFixture::PRODUCT_PREFIX . '1',
            ProductDataFixture::PRODUCT_PREFIX . '72',
            ProductDataFixture::PRODUCT_PREFIX . '69', // main variant
            ProductDataFixture::PRODUCT_PREFIX . '148', // variant
            ProductDataFixture::PRODUCT_PREFIX . '76', // excluded from sale
        ];

        $products = [];

        foreach ($productsReferencesForDomain as $productReference) {
            $products[] = $this->getReference($productReference, Product::class);
        }

        return $products;
    }

    /**
     * @return \App\Model\Category\Category[]
     */
    private function getCategoriesForDomain(): array
    {
        $categoriesReferencesForDomain = [
            CategoryDataFixture::CATEGORY_TV,
            CategoryDataFixture::CATEGORY_COFFEE,
            CategoryDataFixture::CATEGORY_BOOKS,
        ];

        $categories = [];

        foreach ($categoriesReferencesForDomain as $categoryReference) {
            $categories[] = $this->getReference($categoryReference, Category::class);
        }

        return $categories;
    }

    /**
     * @return \App\Model\Product\Brand\Brand[]
     */
    private function getBrandsForDomain(): array
    {
        $brandsReferencesForDomain = [
            BrandDataFixture::BRAND_APPLE,
            BrandDataFixture::BRAND_CANON,
            BrandDataFixture::BRAND_LG,
        ];

        $brands = [];

        foreach ($brandsReferencesForDomain as $brandReference) {
            $brands[] = $this->getReference($brandReference, Brand::class);
        }

        return $brands;
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            CategoryDataFixture::class,
            BrandDataFixture::class,
        ];
    }
}
