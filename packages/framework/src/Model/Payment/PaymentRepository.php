<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class PaymentRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPaymentRepository()
    {
        return $this->em->getRepository(Payment::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForAll()
    {
        return $this->getPaymentRepository()->createQueryBuilder('p')
            ->where('p.deleted = :deleted')->setParameter('deleted', false)
            ->orderBy('p.position')
            ->addOrderBy('p.id');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAll()
    {
        return $this->getQueryBuilderForAll()->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllIncludingDeleted()
    {
        return $this->getPaymentRepository()->findAll();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public function findById($id)
    {
        return $this->getQueryBuilderForAll()
            ->andWhere('p.id = :paymentId')->setParameter('paymentId', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getById($id)
    {
        $payment = $this->findById($id);

        if ($payment === null) {
            throw new PaymentNotFoundException(
                'Payment with ID ' . $id . ' not found.',
            );
        }

        return $payment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllByTransport(Transport $transport)
    {
        return $this->getQueryBuilderForAll()
            ->join('p.transports', 't')
            ->andWhere('t = :transport')->setParameter('transport', $transport)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getOneByUuid(string $uuid): Payment
    {
        $payment = $this->getPaymentRepository()->findOneBy(['uuid' => $uuid]);

        if ($payment === null) {
            throw new PaymentNotFoundException('Payment with UUID ' . $uuid . ' does not exist.');
        }

        return $payment;
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Payment
    {
        $queryBuilder = $this->getPaymentRepository()->createQueryBuilder('p')
            ->join(PaymentDomain::class, 'pd', Join::WITH, 'p.id = pd.payment AND pd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->where('p.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('p.deleted = false')
            ->andWhere('pd.enabled = true')
            ->andWhere('p.hidden = false');

        $payment = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($payment === null) {
            throw new PaymentNotFoundException('Payment with UUID ' . $uuid . ' does not exist.');
        }

        return $payment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @return \App\Model\Payment\Payment[]
     */
    public function getByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod): array
    {
        return $this->getPaymentRepository()->findBy(['goPayPaymentMethod' => $goPayPaymentMethod]);
    }
}
