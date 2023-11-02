<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Payment\Payment;
use App\Model\Payment\Transaction\PaymentTransaction;
use App\Model\Transport\Transport;
use App\Model\Transport\Type\TransportType;
use DateTime;
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
 * @method \App\Model\Order\Order|null findByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findByUuidAndUrlHash(string $uuid, string $urlHash)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuidAndUrlHash(string $uuid, string $urlHash)
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method bool isOrderStatusUsed(\App\Model\Order\Status\OrderStatus $orderStatus)
 * @method \App\Model\Order\Order getByUuid(string $uuid)
 */
class OrderRepository extends BaseOrderRepository
{
    /**
     * @param \DateTime $fromDate
     * @return \App\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTime $fromDate): array
    {
        $queryBuilder = $this->createOrderQueryBuilder()
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

    /**
     * @param \App\Model\Transport\Type\TransportType $transportType
     * @return \App\Model\Order\Order[]
     */
    public function getAllWithoutTrackingNumberByTransportType(TransportType $transportType): array
    {
        $queryBuilder = $this->createOrderQueryBuilder()
            ->join(Transport::class, 't', Join::WITH, 'o.transport = t.id')
            ->andWhere('o.trackingNumber IS NULL')
            ->andWhere('t.transportType = :transportType')
            ->setParameter('transportType', $transportType);

        return $queryBuilder->getQuery()->execute();
    }
}
