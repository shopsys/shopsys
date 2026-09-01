<?php

declare(strict_types=1);

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
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getVatRepository(): EntityRepository
    {
        return $this->em->getRepository(Vat::class);
    }

    protected function getQueryBuilderForAll(string $vatAlias): QueryBuilder
    {
        return $this->getVatRepository()
            ->createQueryBuilder($vatAlias)
            ->orderBy($vatAlias . '.percent');
    }

    public function findById(int $vatId): ?Vat
    {
        return $this->getVatRepository()->find($vatId);
    }

    public function getById(int $vatId): Vat
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

    public function isVatUsed(Vat $vat): bool
    {
        return $this->existsPaymentWithVat($vat)
            || $this->existsTransportWithVat($vat)
            || $this->existsProductWithVat($vat);
    }

    protected function existsPaymentWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(pd.payment)
            FROM ' . PaymentDomain::class . ' pd
            WHERE pd.vat= :vat')
            ->setParameter('vat', $vat);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    protected function existsTransportWithVat(Vat $vat): bool
    {
        $query = $this->em->createQuery('
            SELECT COUNT(td.transport)
            FROM ' . TransportDomain::class . ' td
            WHERE td.vat= :vat')
            ->setParameter('vat', $vat);

        return $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) > 0;
    }

    protected function existsProductWithVat(Vat $vat): bool
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
        $this->replaceProductsVat($oldVat, $newVat);
    }

    protected function replaceProductsVat(Vat $oldVat, Vat $newVat): void
    {
        $this->em->createQueryBuilder()
            ->update(ProductDomain::class, 'pd')
            ->set('pd.vat', ':newVat')->setParameter('newVat', $newVat)
            ->where('pd.vat = :oldVat')->setParameter('oldVat', $oldVat)
            ->getQuery()->execute();
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
