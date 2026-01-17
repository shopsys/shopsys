<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class AutocompleteFavoriteRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteProduct[]
     */
    public function getAutocompleteFavoriteProducts(int $domainId): array
    {
        $queryBuilder = $this->getAutocompleteFavoriteProductsQueryBuilder($domainId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsForDomain(int $domainId): array
    {
        $autocompleteProducts = $this->getAutocompleteFavoriteProducts($domainId);
        $products = [];

        foreach ($autocompleteProducts as $autocompleteProduct) {
            $products[] = $autocompleteProduct->getProduct();
        }

        return $products;
    }

    /**
     * @return int[]
     */
    public function getProductIdsForDomain(int $domainId): array
    {
        $queryBuilder = $this->getAutocompleteFavoriteProductsQueryBuilder($domainId);
        $queryBuilder->select('IDENTITY(afp.product) AS productId');

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'productId');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteCategory[]
     */
    public function getAutocompleteFavoriteCategories(int $domainId): array
    {
        $queryBuilder = $this->getCategoryRepository()
            ->createQueryBuilder('afc')
            ->where('afc.domainId = :domainId')
            ->orderBy('afc.position', 'ASC')
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategoriesForDomain(int $domainId): array
    {
        $autocompleteCategories = $this->getAutocompleteFavoriteCategories($domainId);
        $categories = [];

        foreach ($autocompleteCategories as $autocompleteCategory) {
            $categories[] = $autocompleteCategory->getCategory();
        }

        return $categories;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesForDomain(int $domainId, int $limit): array
    {
        return $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->join(AutocompleteFavoriteCategory::class, 'afc', Join::WITH, 'afc.category = c AND afc.domainId = :domainId')
            ->orderBy('afc.position')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteBrand[]
     */
    public function getAutocompleteFavoriteBrands(int $domainId, ?int $limit = null): array
    {
        $queryBuilder = $this->getBrandRepository()
            ->createQueryBuilder('afb')
            ->where('afb.domainId = :domainId')
            ->orderBy('afb.position', 'ASC')
            ->setParameter('domainId', $domainId);

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsForDomain(int $domainId, ?int $limit = null): array
    {
        $autocompleteBrands = $this->getAutocompleteFavoriteBrands($domainId, $limit);
        $brands = [];

        foreach ($autocompleteBrands as $autocompleteBrand) {
            $brands[] = $autocompleteBrand->getBrand();
        }

        return $brands;
    }

    protected function getProductRepository(): EntityRepository
    {
        return $this->em->getRepository(AutocompleteFavoriteProduct::class);
    }

    protected function getCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(AutocompleteFavoriteCategory::class);
    }

    protected function getBrandRepository(): EntityRepository
    {
        return $this->em->getRepository(AutocompleteFavoriteBrand::class);
    }

    protected function getAutocompleteFavoriteProductsQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getProductRepository()
            ->createQueryBuilder('afp')
            ->where('afp.domainId = :domainId')
            ->orderBy('afp.position', 'ASC')
            ->setParameter('domainId', $domainId);
    }
}
