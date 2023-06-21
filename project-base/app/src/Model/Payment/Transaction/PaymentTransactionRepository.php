<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction;

use App\Model\Payment\Transaction\Exception\PaymentTransactionNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PaymentTransactionRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(PaymentTransaction::class);
    }

    /**
     * @param int $id
     * @return \App\Model\Payment\Transaction\PaymentTransaction
     */
    public function getById(int $id): PaymentTransaction
    {
        $paymentTransaction = $this->getRepository()->find($id);

        if ($paymentTransaction === null) {
            throw new PaymentTransactionNotFoundException(sprintf('Payment transaction id %d not found.', $id));
        }

        return $paymentTransaction;
    }
}
