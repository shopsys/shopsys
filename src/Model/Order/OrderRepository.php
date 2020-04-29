<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Payment\Payment;
use Doctrine\ORM\Query\Expr\Join;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository as BaseOrderRepository;

/**
 * @method \App\Model\Order\Order[] getOrdersByCustomerUserId(int $customerUserId)
 * @method \App\Model\Order\Order|null findLastByCustomerUserId(int $customerUserId)
 * @method \App\Model\Order\Order|null findById(int $id)
 * @method \App\Model\Order\Order getById(int $id)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomerUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findByUrlHashIncludingDeletedOrders(string $urlHash)
 */
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
