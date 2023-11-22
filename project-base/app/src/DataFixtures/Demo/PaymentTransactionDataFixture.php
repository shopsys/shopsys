<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

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
        private PaymentTransactionDataFactory $paymentTransactionDataFactory,
        private PaymentTransactionFacade $paymentTransactionFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_CZ);
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();
        $paymentTransactionData->externalPaymentIdentifier = 'TR-123456';
        $paymentTransactionData->externalPaymentStatus = 'CREATED';
        $this->paymentTransactionFacade->create($paymentTransactionData);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            PaymentDataFixture::class,
            OrderDataFixture::class,
        ];
    }
}
