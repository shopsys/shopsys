<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Doctrine\ORM\EntityManagerInterface;

class PaymentTransactionFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PaymentTransactionRepository $paymentTransactionRepository,
        protected readonly PaymentTransactionFactory $paymentTransactionFactory,
    ) {
    }

    public function create(PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $paymentTransaction = $this->paymentTransactionFactory->create($paymentTransactionData);
        $this->em->persist($paymentTransaction);
        $this->em->flush();

        $paymentTransactionData->order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        return $paymentTransaction;
    }

    public function edit(int $id, PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $paymentTransaction = $this->paymentTransactionRepository->getById($id);
        $paymentTransaction->edit($paymentTransactionData);
        $this->em->flush();

        return $paymentTransaction;
    }

    public function getById(int $id): PaymentTransaction
    {
        return $this->paymentTransactionRepository->getById($id);
    }
}
