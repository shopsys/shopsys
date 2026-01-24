<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class PaymentRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getPaymentRepository(): EntityRepository
    {
        return $this->em->getRepository(Payment::class);
    }

    public function getQueryBuilderForAll(): QueryBuilder
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

    public function findById(int $id): ?Payment
    {
        return $this->getQueryBuilderForAll()
            ->andWhere('p.id = :paymentId')->setParameter('paymentId', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getById(int $id): Payment
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

    public function getOneByUuid(string $uuid): Payment
    {
        $payment = $this->getPaymentRepository()->findOneBy(['uuid' => $uuid]);

        if ($payment === null) {
            throw new PaymentNotFoundException('Payment with UUID ' . $uuid . ' does not exist.');
        }

        return $payment;
    }

    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Payment
    {
        $queryBuilder = $this->getPaymentRepository()->createQueryBuilder('p')
            ->join(PaymentDomain::class, 'pd', Join::WITH, 'p.id = pd.payment AND pd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->where('p.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('p.deleted = false')
            ->andWhere('pd.enabled = true')
            ->andWhere('p.hidden = false')
            ->andWhere('pd.hiddenByGoPay = false');

        $payment = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($payment === null) {
            throw new PaymentNotFoundException('Payment with UUID ' . $uuid . ' does not exist.');
        }

        return $payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod, int $domainId): array
    {
        return $this->getPaymentRepository()
            ->createQueryBuilder('p')
            ->join(PaymentDomain::class, 'pd', Join::WITH, 'p.id = pd.payment AND pd.domainId = :domainId')
            ->where('pd.goPayPaymentMethod = :goPayPaymentMethod')
            ->setParameter('domainId', $domainId)
            ->setParameter('goPayPaymentMethod', $goPayPaymentMethod)
            ->getQuery()
            ->execute();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllWithEagerLoadedDomainsAndTranslations(DomainConfig $domainConfig): array
    {
        return $this->getQueryBuilderForAll()
            ->addSelect('pd')
            ->addSelect('pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->join('p.domains', 'pd', Join::WITH, 'pd.domainId = :domainId')
            ->setParameter('locale', $domainConfig->getLocale())
            ->setParameter('domainId', $domainConfig->getId())
            ->getQuery()->execute();
    }

    public function findPaymentByExternalMethodTransportAndDomainId(
        string $externalPaymentMethod,
        Transport $transport,
        int $domainId,
    ): ?Payment {
        return $this->getQueryBuilderForAll()
            ->join('p.transports', 't', Join::WITH)
            ->andWhere('t = :transport')->setParameter('transport', $transport)
            ->join(PaymentDomain::class, 'pd', Join::WITH, 'pd.payment = p AND pd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->join('pd.goPayPaymentMethod', 'gppm', Join::WITH, 'gppm.identifier = :goPayPaymentMethod AND gppm.domainId = :domainId')
            ->setParameter('goPayPaymentMethod', $externalPaymentMethod)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
