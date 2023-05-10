<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction;

use Doctrine\ORM\EntityManagerInterface;

class PaymentTransactionFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Payment\Transaction\PaymentTransactionRepository
     */
    private PaymentTransactionRepository $paymentTransactionRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Payment\Transaction\PaymentTransactionRepository $paymentTransactionRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        $this->em = $em;
        $this->paymentTransactionRepository = $paymentTransactionRepository;
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return \App\Model\Payment\Transaction\PaymentTransaction
     */
    public function create(PaymentTransactionData $paymentTransactionData): PaymentTransaction
    {
        $paymentTransaction = new PaymentTransaction($paymentTransactionData);
        $this->em->persist($paymentTransaction);
        $this->em->flush();

        $paymentTransactionData->order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        return $paymentTransaction;
    }

    /**
     * @param int $id
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     * @return \App\Model\Payment\Transaction\PaymentTransaction
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
     * @return \App\Model\Payment\Transaction\PaymentTransaction
     */
    public function getById(int $id): PaymentTransaction
    {
        return $this->paymentTransactionRepository->getById($id);
    }
}
