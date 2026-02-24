<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;

class GoPayRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderRepository $orderRepository,
    ) {
    }

    protected function getQueryBuilderForAllUnpaidGoPayOrders(DateTimeImmutable $fromDate): QueryBuilder
    {
        $queryBuilder = $this->orderRepository->createOrderQueryBuilder()
            ->join('o.items', 'oi', Join::WITH, 'oi.payment IS NOT NULL')
            ->join('oi.payment', 'p')
            ->join('o.paymentTransactions', 'pt', Join::WITH, 'p.id = pt.payment')
            ->andWhere('p.type = :type')
            ->andWhere('o.createdAt >= :fromDate')
            ->andWhere('pt.externalPaymentStatus NOT IN (:paymentStatuses)')
            ->orderBy('o.createdAt', 'ASC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('paymentStatuses', [PaymentStatus::PAID, PaymentStatus::CANCELED, PaymentStatus::TIMEOUTED])
            ->setParameter('type', PaymentTypeEnum::TYPE_GOPAY);

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTimeImmutable $fromDate): array
    {
        return $this->getQueryBuilderForAllUnpaidGoPayOrders($fromDate)
            ->getQuery()
            ->execute();
    }
}
