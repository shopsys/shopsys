<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Category\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class PromoCodeCategoryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder()
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param int $promoCodeId
     * @return \App\Model\Order\PromoCode\PromoCodeCategory[]
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
     * @return \App\Model\Category\Category[]
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
}
