<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Listing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Order\Order;

class OrderListAdminRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilder($locale)
    {
        return $this->em->createQueryBuilder()
            ->select('
                o.id,
                o.number,
                o.domainId,
                o.createdAt,
                MAX(ost.name) AS statusName,
                o.totalPriceWithVat,
                (CASE WHEN o.companyName IS NOT NULL
                    THEN o.companyName
                    ELSE CONCAT(o.lastName, \' \', o.firstName)
                END) AS customerName')
            ->from(Order::class, 'o')
            ->where('o.deleted = :deleted')
            ->join('o.status', 'os')
            ->join('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->groupBy('o.id')
            ->setParameter('deleted', false)
            ->setParameter('locale', $locale);
    }
}
