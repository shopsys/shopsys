<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class VatRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllIncludingMarkedForDeletion()
    {
        return $this->getVatRepository()->findAll();
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
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllExceptId($vatId)
    {
        $qb = $this->getQueryBuilderForAll('v')
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
            LEFT JOIN ' . Product::class . ' p WITH p.vat = v
            WHERE v.replaceWith IS NOT NULL
            GROUP BY v
            HAVING COUNT(p) = 0');

        return $query->getResult();
    }

    public function isVatUsed(Vat $vat)
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
            SELECT COUNT(p)
            FROM ' . Payment::class . ' p
            WHERE p.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @return bool
     */
    protected function existsTransportWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(t)
            FROM ' . Transport::class . ' t
            WHERE t.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    /**
     * @return bool
     */
    protected function existsProductWithVat(Vat $vat)
    {
        $query = $this->em->createQuery('
            SELECT COUNT(p)
            FROM ' . Product::class . ' p
            WHERE p.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    public function replaceVat(Vat $oldVat, Vat $newVat)
    {
        $this->replacePaymentsVat($oldVat, $newVat);
        $this->replaceTransportsVat($oldVat, $newVat);
    }

    protected function replacePaymentsVat(Vat $oldVat, Vat $newVat)
    {
        $this->em->createQueryBuilder()
            ->update(Payment::class, 'p')
            ->set('p.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('p.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    protected function replaceTransportsVat(Vat $oldVat, Vat $newVat)
    {
        $this->em->createQueryBuilder()
            ->update(Transport::class, 't')
            ->set('t.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('t.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }
}
