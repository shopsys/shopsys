<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\Order;
use App\Model\Payment\Transaction\PaymentTransactionDataFactory;
use App\Model\Payment\Transaction\PaymentTransactionFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class PaymentTransactionDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     * @param \App\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
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
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1);
        $this->createPaymentTransaction($order, 'TR-123456', 'CREATED');

        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14);
        $this->createPaymentTransaction($order, '12454321', 'CREATED');
        $this->createPaymentTransaction($order, '52467431', 'CREATED');
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
