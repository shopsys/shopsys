<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;

class GoPayRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderRepository $orderRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTimeImmutable $fromDate): array
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
            ->setParameter('type', PaymentTypeEnum::TYPE_GOPAY);

        return $queryBuilder->getQuery()->execute();
    }
}
