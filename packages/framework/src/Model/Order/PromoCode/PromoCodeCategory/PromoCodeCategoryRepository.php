<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;

class PromoCodeCategoryRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategory[]
     */
    public function getAllByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('pcc')
            ->from(PromoCodeCategory::class, 'pcc')
            ->where('pcc.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategoriesByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('c')
            ->from(PromoCodeCategory::class, 'pcc')
            ->join(Category::class, 'c', Join::WITH, 'pcc.category = c')
            ->where('pcc.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @param int $domainId
     * @return int[]
     */
    public function getProductIdsFromCategoriesByPromoCodeIdAndDomainId(int $promoCodeId, int $domainId): array
    {
        $result = $this->getQueryBuilder()
            ->select('IDENTITY(pcd.product) as id')
            ->from(PromoCodeCategory::class, 'pcc')
            ->join(
                ProductCategoryDomain::class,
                'pcd',
                Join::WITH,
                'pcc.category = pcd.category AND pcd.domainId = :domainId',
            )
            ->where('pcc.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();

        return array_column($result, 'id');
    }
}
