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

    protected function getVatRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Vat::class);
    }

    protected function getQueryBuilderForAll(string $vatAlias): \Doctrine\ORM\QueryBuilder
    {
        return $this->getVatRepository()
            ->createQueryBuilder($vatAlias)
            ->where($vatAlias . '.replaceWith IS NULL')
            ->orderBy($vatAlias . '.percent');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAll(): array
    {
        return $this->getQueryBuilderForAll('v')->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllIncludingMarkedForDeletion(): array
    {
        return $this->getVatRepository()->findAll();
    }

    public function findById(int $vatId): ?\Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        return $this->getVatRepository()->find($vatId);
    }

    public function getById(int $vatId): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        $vat = $this->findById($vatId);

        if ($vat === null) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException('Vat with ID ' . $vatId . ' not found.');
        }

        return $vat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllExceptId(int $vatId): array
    {
        $qb = $this->getQueryBuilderForAll('v')
            ->andWhere('v.id != :id')
            ->setParameter('id', $vatId);

        return $qb->getQuery()->getResult();
    }

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

    protected function existsPaymentWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(p)
            FROM ' . Payment::class . ' p
            WHERE p.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    protected function existsTransportWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(t)
            FROM ' . Transport::class . ' t
            WHERE t.vat= :vat')
            ->setParameter('vat', $vat);
        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    protected function existsProductWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(p)
            FROM ' . Product::class . ' p
            WHERE p.vat= :vat')
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
            ->update(Payment::class, 'p')
            ->set('p.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('p.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }

    protected function replaceTransportsVat(Vat $oldVat, Vat $newVat): void
    {
        $this->em->createQueryBuilder()
            ->update(Transport::class, 't')
            ->set('t.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('t.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
    }
}
