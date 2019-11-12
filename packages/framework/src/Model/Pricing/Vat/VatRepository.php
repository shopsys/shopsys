<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Shopsys\FrameworkBundle\Model\Transport\TransportPrice;

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
    public function getAll()
    {
        return $this->getQueryBuilderForAll('v')->getQuery()->getResult();
    }

    /**
     * @param int $domainId
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
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException('Vat with ID ' . $vatId . ' not found.');
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     */
    public function isVatUsed(Vat $vat)
    {
        return $this->existsPaymentWithVat($vat)
            || $this->existsTransportWithVat($vat)
            || $this->existsProductWithVat($vat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    protected function existsPaymentWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(pp.payment)
            FROM ' . PaymentPrice::class . ' pp
            WHERE pp.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    protected function existsTransportWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(tp.transport)
            FROM ' . TransportPrice::class . ' tp
            WHERE tp.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $oldVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    public function replaceVat(Vat $oldVat, Vat $newVat)
    {
        $this->replacePaymentsPriceVat($oldVat, $newVat);
        $this->replaceTransportsPriceVat($oldVat, $newVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $oldVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    protected function replacePaymentsPriceVat(Vat $oldVat, Vat $newVat)
    {
        $this->em->createQueryBuilder()
            ->update(PaymentPrice::class, 'pp')
            ->set('pp.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('pp.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $oldVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    protected function replaceTransportsPriceVat(Vat $oldVat, Vat $newVat)
    {
        $this->em->createQueryBuilder()
            ->update(TransportPrice::class, 'tp')
            ->set('tp.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('tp.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomain(int $domainId): array
    {
        return $this->getVatRepository()->findBy(['domainId' => $domainId]);
    }
}
