<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;

class PaymentTransactionDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     */
    public function __construct(
        private readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        private readonly PaymentTransactionFacade $paymentTransactionFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->createPaymentTransaction($order, 'TR-123456', PaymentStatus::CREATED);

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class);
        $this->createPaymentTransaction($order, '12454321', PaymentStatus::CREATED);
        $this->createPaymentTransaction($order, '52467431', PaymentStatus::CREATED);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            PaymentDataFixture::class,
            OrderDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $externalPaymentIdentifier
     * @param string $paymentStatus
     */
    public function createPaymentTransaction(
        Order $order,
        string $externalPaymentIdentifier,
        string $paymentStatus,
    ): void {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();
        $paymentTransactionData->externalPaymentIdentifier = $externalPaymentIdentifier;
        $paymentTransactionData->externalPaymentStatus = $paymentStatus;
        $this->paymentTransactionFacade->create($paymentTransactionData);
    }
}
