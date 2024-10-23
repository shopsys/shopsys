<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductRepository(): EntityRepository
    {
        return $this->em->getRepository(Product::class);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsByUuidsIndexedByUuid(array $uuids): array
    {
        $queryBuilder = $this->getProductRepository()->createQueryBuilder('p')
            ->select('p, pt, pd, v, u')
            ->join('p.translations', 'pt')
            ->join('p.domains', 'pd', Join::WITH, 'pd.domainId = :domainId')
            ->join('pd.vat', 'v')
            ->join('p.unit', 'u')
            ->where('p.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->setParameter('domainId', $this->domain->getId())
            ->indexBy('p', 'p.uuid');

        return $queryBuilder->getQuery()->getResult();
    }
}
