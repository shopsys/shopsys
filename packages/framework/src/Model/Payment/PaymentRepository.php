<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class PaymentRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getPaymentRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Payment::class);
    }

    public function getQueryBuilderForAll(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getPaymentRepository()->createQueryBuilder('p')
            ->where('p.deleted = :deleted')->setParameter('deleted', false)
            ->orderBy('p.position')
            ->addOrderBy('p.id');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAll(): array
    {
        return $this->getQueryBuilderForAll()->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllIncludingDeleted(): array
    {
        return $this->getPaymentRepository()->findAll();
    }

    /**
     * @param int $id
     */
    public function findById($id): ?\Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        return $this->getQueryBuilderForAll()
            ->andWhere('p.id = :paymentId')->setParameter('paymentId', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     */
    public function getById($id): \Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        $payment = $this->findById($id);
        if ($payment === null) {
            throw new \Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException(
                'Payment with ID ' . $id . ' not found.'
            );
        }

        return $payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllByTransport(Transport $transport): array
    {
        return $this->getQueryBuilderForAll()
            ->join('p.transports', 't')
            ->andWhere('t = :transport')->setParameter('transport', $transport)
            ->getQuery()
            ->getResult();
    }
}
