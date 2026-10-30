<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class LuigisBoxBrandRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrands(?int $lastSeekId, int $maxResults): array
    {
        $queryBuilder = $this->em->getRepository(Brand::class)
            ->createQueryBuilder('b')
            ->orderBy('b.id')
            ->setMaxResults($maxResults);

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('b.id > :lastBrandId')->setParameter('lastBrandId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
