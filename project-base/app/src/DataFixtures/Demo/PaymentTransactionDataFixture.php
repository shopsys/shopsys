<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use GoPay\Definition\Response\PaymentStatus;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;

class PaymentTransactionDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        private readonly PaymentTransactionFacade $paymentTransactionFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->createPaymentTransaction($order, 'TR-123456', PaymentStatus::CREATED);

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class);
        $this->createPaymentTransaction($order, '12454321', PaymentStatus::CREATED);
        $this->createPaymentTransaction($order, '52467431', PaymentStatus::CREATED);
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            PaymentDataFixture::class,
            OrderDataFixture::class,
        ];
    }

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
