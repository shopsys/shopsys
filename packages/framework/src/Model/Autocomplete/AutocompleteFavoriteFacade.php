<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\EntityManagerInterface;

class AutocompleteFavoriteFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AutocompleteFavoriteRepository $autocompleteFavoriteRepository,
        protected readonly AutocompleteFavoriteProductFactory $autocompleteFavoriteProductFactory,
        protected readonly AutocompleteFavoriteCategoryFactory $autocompleteFavoriteCategoryFactory,
        protected readonly AutocompleteFavoriteBrandFactory $autocompleteFavoriteBrandFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForDomain(int $domainId): array
    {
        return $this->autocompleteFavoriteRepository->getProductsForDomain($domainId);
    }

    /**
     * @return int[]
     */
    public function getProductIdsForDomain(int $domainId): array
    {
        return $this->autocompleteFavoriteRepository->getProductIdsForDomain($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategoriesForDomain(int $domainId): array
    {
        return $this->autocompleteFavoriteRepository->getCategoriesForDomain($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesForDomain(int $domainId, int $limit): array
    {
        return $this->autocompleteFavoriteRepository->getVisibleCategoriesForDomain($domainId, $limit);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsForDomain(int $domainId, ?int $limit = null): array
    {
        return $this->autocompleteFavoriteRepository->getBrandsForDomain($domainId, $limit);
    }

    public function saveAllForDomain(int $domainId, AutocompleteFavoriteData $autocompleteFavoriteData): void
    {
        $this->saveProductsForDomain($domainId, $autocompleteFavoriteData->products);
        $this->saveCategoriesForDomain($domainId, $autocompleteFavoriteData->categories);
        $this->saveBrandsForDomain($domainId, $autocompleteFavoriteData->brands);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function saveProductsForDomain(int $domainId, array $products): void
    {
        $existingProducts = $this->autocompleteFavoriteRepository->getAutocompleteFavoriteProducts($domainId);

        foreach ($existingProducts as $existingProduct) {
            $this->em->remove($existingProduct);
        }
        $this->em->flush();

        $position = 1;

        foreach ($products as $product) {
            $autocompleteFavoriteProduct = $this->autocompleteFavoriteProductFactory->create($product, $domainId, $position);
            $this->em->persist($autocompleteFavoriteProduct);
            $position++;
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    protected function saveCategoriesForDomain(int $domainId, array $categories): void
    {
        $existingCategories = $this->autocompleteFavoriteRepository->getAutocompleteFavoriteCategories($domainId);

        foreach ($existingCategories as $existingCategory) {
            $this->em->remove($existingCategory);
        }
        $this->em->flush();

        $position = 1;

        foreach ($categories as $category) {
            $autocompleteFavoriteCategory = $this->autocompleteFavoriteCategoryFactory->create($category, $domainId, $position);
            $this->em->persist($autocompleteFavoriteCategory);
            $position++;
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brands
     */
    protected function saveBrandsForDomain(int $domainId, array $brands): void
    {
        $existingBrands = $this->autocompleteFavoriteRepository->getAutocompleteFavoriteBrands($domainId);

        foreach ($existingBrands as $existingBrand) {
            $this->em->remove($existingBrand);
        }
        $this->em->flush();

        $position = 1;

        foreach ($brands as $brand) {
            $autocompleteFavoriteBrand = $this->autocompleteFavoriteBrandFactory->create($brand, $domainId, $position);
            $this->em->persist($autocompleteFavoriteBrand);
            $position++;
        }
        $this->em->flush();
    }
}
