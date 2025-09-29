<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AutocompleteFavoriteRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteProduct[]
     */
    public function getAllAutocompleteFavoriteProducts(int $domainId): array
    {
        return $this->getProductRepository()
            ->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForDomain(int $domainId): array
    {
        $autocompleteProducts = $this->getAllAutocompleteFavoriteProducts($domainId);
        $products = [];

        foreach ($autocompleteProducts as $autocompleteProduct) {
            $products[] = $autocompleteProduct->getProduct();
        }

        return $products;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteCategory[]
     */
    public function getAllAutocompleteFavoriteCategories(int $domainId): array
    {
        return $this->getCategoryRepository()
            ->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategoriesForDomain(int $domainId): array
    {
        $autocompleteCategories = $this->getAllAutocompleteFavoriteCategories($domainId);
        $categories = [];

        foreach ($autocompleteCategories as $autocompleteCategory) {
            $categories[] = $autocompleteCategory->getCategory();
        }

        return $categories;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteBrand[]
     */
    public function getAllAutocompleteFavoriteBrands(int $domainId): array
    {
        return $this->getBrandRepository()
            ->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsForDomain(int $domainId): array
    {
        $autocompleteBrands = $this->getAllAutocompleteFavoriteBrands($domainId);
        $brands = [];

        foreach ($autocompleteBrands as $autocompleteBrand) {
            $brands[] = $autocompleteBrand->getBrand();
        }

        return $brands;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductRepository(): EntityRepository
    {
        return $this->em->getRepository(AutocompleteFavoriteProduct::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(AutocompleteFavoriteCategory::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBrandRepository(): EntityRepository
    {
        return $this->em->getRepository(AutocompleteFavoriteBrand::class);
    }
}
