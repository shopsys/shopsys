<?php

namespace App\Model\Order;

use Doctrine\ORM\Query\Expr\Join;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository as BaseOrderRepository;
use App\Model\Payment\Payment;

class OrderRepository extends BaseOrderRepository
{
    /**
     * @param \DateTime $fromDate
     * @return \App\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(\DateTime $fromDate): array
    {
        $queryBuilder = $this->createOrderQueryBuilder()
            ->join(Payment::class, 'p', Join::WITH, 'o.payment = p.id')
            ->andWhere('p.type = :type AND (o.goPayStatus != :statusPaid OR o.goPayStatus IS NULL)')
            ->andWhere('o.goPayId IS NOT NULL')
            ->andWhere('o.createdAt >= :fromDate')
            ->orderBy('o.createdAt', 'ASC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('statusPaid', PaymentStatus::PAID)
            ->setParameter('type', Payment::TYPE_GOPAY);

        return $queryBuilder->getQuery()->execute();
    }
}
