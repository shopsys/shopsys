<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Brand;

use App\Model\Product\Brand\Brand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class BrandRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function getAllWithDomainsAndTranslations(DomainConfig $domainConfig): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('b, bd, bt')
            ->from(Brand::class, 'b')
            ->join('b.domains', 'bd', Join::WITH, 'bd.domainId = :domainId')
            ->join('b.translations', 'bt', Join::WITH, 'bt.locale = :locale')
            ->setParameter('domainId', $domainConfig->getId())
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy('b.name')
            ->getQuery()->getResult();
    }
}
