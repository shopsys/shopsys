<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDomain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Shopsys\FrameworkBundle\Model\Transport\TransportDomain;

class VatRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getVatRepository(): EntityRepository
    {
        return $this->em->getRepository(Vat::class);
    }

    /**
     * @param string $vatAlias
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilderForAll(string $vatAlias): QueryBuilder
    {
        return $this->getVatRepository()
            ->createQueryBuilder($vatAlias)
            ->where($vatAlias . '.replaceWith IS NULL')
            ->orderBy($vatAlias . '.percent');
    }

    /**
     * @param int $domainId
     * @return object[]
     */
    public function getAllForDomainIncludingMarkedForDeletion(int $domainId): array
    {
        return $this->getVatRepository()->findBy(['domainId' => $domainId]);
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function findById(int $vatId): ?Vat
    {
        return $this->getVatRepository()->find($vatId);
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getById(int $vatId): Vat
    {
        $vat = $this->findById($vatId);

        if ($vat === null) {
            throw new VatNotFoundException('Vat with ID ' . $vatId . ' not found.');
        }

        return $vat;
    }

    /**
     * @param int $domainId
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomainExceptId(int $domainId, int $vatId): array
    {
        $qb = $this->getQueryBuilderForAll('v')
            ->andWhere('v.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->andWhere('v.id != :id')
            ->setParameter('id', $vatId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    public function existsVatToBeReplacedWith(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(v)
            FROM ' . Vat::class . ' v
            WHERE v.replaceWith = :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getVatsWithoutProductsMarkedForDeletion(): array
    {
        $query = $this->em->createQuery('
            SELECT v
            FROM ' . Vat::class . ' v
            LEFT JOIN ' . ProductDomain::class . ' pd WITH pd.vat = v
            WHERE v.replaceWith IS NOT NULL
            GROUP BY v
            HAVING COUNT(pd) = 0');

        return $query->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    public function isVatUsed(Vat $vat): bool
    {
        return $this->existsPaymentWithVat($vat)
            || $this->existsTransportWithVat($vat)
            || $this->existsProductWithVat($vat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    protected function existsPaymentWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(pd.payment)
            FROM ' . PaymentDomain::class . ' pd
            WHERE pd.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    protected function existsTransportWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(td.transport)
            FROM ' . TransportDomain::class . ' td
            WHERE td.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    protected function existsProductWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(pd)
            FROM ' . ProductDomain::class . ' pd
            WHERE pd.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $oldVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    public function replaceVat(Vat $oldVat, Vat $newVat): void
    {
        $this->replacePaymentsVat($oldVat, $newVat);
        $this->replaceTransportsVat($oldVat, $newVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $oldVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    protected function replacePaymentsVat(Vat $oldVat, Vat $newVat): void
    {
        $this->em->createQueryBuilder()
            ->update(PaymentDomain::class, 'pd')
            ->set('pd.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('pd.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $oldVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    protected function replaceTransportsVat(Vat $oldVat, Vat $newVat): void
    {
        $this->em->createQueryBuilder()
            ->update(TransportDomain::class, 'td')
            ->set('td.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('td.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @return object[]
     */
    public function getAllForDomain(int $domainId): array
    {
        return $this->getVatRepository()->findBy(['domainId' => $domainId]);
    }
}
