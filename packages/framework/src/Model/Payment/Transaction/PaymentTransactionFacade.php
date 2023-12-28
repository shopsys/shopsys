<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Doctrine\ORM\EntityManagerInterface;

class PaymentTransactionFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionRepository $paymentTransactionRepository
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFactory $paymentTransactionFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PaymentTransactionRepository $paymentTransactionRepository,
        protected readonly PaymentTransactionFactory $paymentTransactionFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction
     */
    public function create(PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $paymentTransaction = $this->paymentTransactionFactory->create($paymentTransactionData);
        $this->em->persist($paymentTransaction);
        $this->em->flush();

        $paymentTransactionData->order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        return $paymentTransaction;
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction
     */
    public function edit(int $id, PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $paymentTransaction = $this->paymentTransactionRepository->getById($id);
        $paymentTransaction->edit($paymentTransactionData);
        $this->em->flush();

        return $paymentTransaction;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction
     */
    public function getById(int $id): PaymentTransaction
    {
        return $this->paymentTransactionRepository->getById($id);
    }
}
