<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class BrandRepository
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
    protected function getBrandRepository(): EntityRepository
    {
        return $this->em->getRepository(Brand::class);
    }

    /**
     * @param string[] $brandNames
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsByNames(array $brandNames): array
    {
        $queryBuilder = $this->getBrandRepository()
            ->createQueryBuilder('b')
            ->andWhere('b.name IN(:brandNames)');
        $queryBuilder->setParameter('brandNames', $brandNames);
        $queryBuilder->orderBy(OrderByCollationHelper::createOrderByForLocale('b.name', $this->domain->getLocale()));

        return $queryBuilder->getQuery()->getResult();
    }
}
