<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;

class GoPayRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderRepository $orderRepository,
    ) {
    }

    /**
     * @param \DateTime $fromDate
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTime $fromDate): array
    {
        $queryBuilder = $this->orderRepository->createOrderQueryBuilder()
            ->join(Payment::class, 'p', Join::WITH, 'o.payment = p.id')
            ->join(PaymentTransaction::class, 'pt', Join::WITH, 'o.id = pt.order AND p.id = pt.payment')
            ->andWhere('p.type = :type')
            ->andWhere('o.createdAt >= :fromDate')
            ->andWhere('pt.externalPaymentStatus NOT IN (:paymentStatuses)')
            ->orderBy('o.createdAt', 'ASC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('paymentStatuses', [PaymentStatus::PAID, PaymentStatus::CANCELED, PaymentStatus::TIMEOUTED])
            ->setParameter('type', Payment::TYPE_GOPAY);

        return $queryBuilder->getQuery()->execute();
    }
}
