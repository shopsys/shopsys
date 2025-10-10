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
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteProduct[]
     */
    public function getAutocompleteFavoriteProducts(int $domainId): array
    {
        $queryBuilder = $this->getAutocompleteFavoriteProductsQueryBuilder($domainId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $domainId
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
     * @param int $domainId
     * @return int[]
     */
    public function getProductIdsForDomain(int $domainId): array
    {
        $queryBuilder = $this->getAutocompleteFavoriteProductsQueryBuilder($domainId);
        $queryBuilder->select('IDENTITY(afp.product) AS productId');

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'productId');
    }

    /**
     * @param int $domainId
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
     * @param int $domainId
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
     * @param int $domainId
     * @param int $limit
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
     * @param int $domainId
     * @param int|null $limit
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
     * @param int $domainId
     * @param int|null $limit
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

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAutocompleteFavoriteProductsQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getProductRepository()
            ->createQueryBuilder('afp')
            ->where('afp.domainId = :domainId')
            ->orderBy('afp.position', 'ASC')
            ->setParameter('domainId', $domainId);
    }
}
