<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDomain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Shopsys\FrameworkBundle\Model\Transport\TransportDomain;

class VatRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getVatRepository()
    {
        return $this->em->getRepository(Vat::class);
    }

    /**
     * @param string $vatAlias
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilderForAll($vatAlias)
    {
        return $this->getVatRepository()
            ->createQueryBuilder($vatAlias)
            ->where($vatAlias . '.replaceWith IS NULL')
            ->orderBy($vatAlias . '.percent');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomainIncludingMarkedForDeletion(int $domainId): array
    {
        return $this->getVatRepository()->findBy(['domainId' => $domainId]);
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function findById($vatId)
    {
        return $this->getVatRepository()->find($vatId);
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getById($vatId)
    {
        $vat = $this->findById($vatId);

        if ($vat === null) {
            throw new VatNotFoundException('Vat with ID ' . $vatId . ' not found.');
        }

        return $vat;
    }

    /**
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
     * @return bool
     */
    public function existsVatToBeReplacedWith(Vat $vat)
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
    public function getVatsWithoutProductsMarkedForDeletion()
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

    public function isVatUsed(Vat $vat): bool
    {
        return $this->existsPaymentWithVat($vat)
            || $this->existsTransportWithVat($vat)
            || $this->existsProductWithVat($vat);
    }

    /**
     * @return bool
     */
    protected function existsPaymentWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(pd.payment)
            FROM ' . PaymentDomain::class . ' pd
            WHERE pd.vat= :vat')
            ->setParameter('vat', $vat);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @return bool
     */
    protected function existsTransportWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(td.transport)
            FROM ' . TransportDomain::class . ' td
            WHERE td.vat= :vat')
            ->setParameter('vat', $vat);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @return bool
     */
    protected function existsProductWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(pd)
            FROM ' . ProductDomain::class . ' pd
            WHERE pd.vat= :vat')
            ->setParameter('vat', $vat);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    public function replaceVat(Vat $oldVat, Vat $newVat): void
    {
        $this->replacePaymentsVat($oldVat, $newVat);
        $this->replaceTransportsVat($oldVat, $newVat);
    }

    protected function replacePaymentsVat(Vat $oldVat, Vat $newVat): void
    {
        $this->em->createQueryBuilder()
            ->update(PaymentDomain::class, 'pd')
            ->set('pd.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('pd.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    protected function replaceTransportsVat(Vat $oldVat, Vat $newVat): void
    {
        $this->em->createQueryBuilder()
            ->update(TransportDomain::class, 'td')
            ->set('td.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('td.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomain(int $domainId): array
    {
        return $this->getVatRepository()->findBy(['domainId' => $domainId]);
    }
}
