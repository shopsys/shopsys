<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use GoPay\Definition\Response\PaymentStatus;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Order\OrderPaidStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;

class OrderPaidDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderPaidStatusFacade $orderPaidStatusFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getOrdersToMarkAsPaid() as $order) {
            $this->orderPaidStatusFacade->markOrderAsPaid($order);
        }
    }

    /**
     * @return \App\Model\Order\Order[]
     */
    private function getOrdersToMarkAsPaid(): array
    {
        $orderIdsReservedForPaymentChangeTests = [
            $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class)->getId(),
            $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class)->getId(),
        ];

        return $this->em->createQueryBuilder()
            ->select('o')
            ->distinct()
            ->from(Order::class, 'o')
            ->join('o.status', 'os')
            ->leftJoin('o.paymentTransactions', 'pt')
            ->leftJoin('pt.payment', 'p')
            ->where('(p.type = :paymentTypeGoPay AND pt.externalPaymentStatus = :externalPaymentStatusPaid) OR os.type = :statusTypeDone')
            ->andWhere('o.id NOT IN (:orderIdsReservedForPaymentChangeTests)')
            ->setParameter('paymentTypeGoPay', PaymentTypeEnum::TYPE_GOPAY)
            ->setParameter('externalPaymentStatusPaid', PaymentStatus::PAID)
            ->setParameter('statusTypeDone', OrderStatusTypeEnum::TYPE_DONE)
            ->setParameter('orderIdsReservedForPaymentChangeTests', $orderIdsReservedForPaymentChangeTests)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            OrderDataFixture::class,
            CompanyOrderDataFixture::class,
            PaymentTransactionDataFixture::class,
        ];
    }
}
