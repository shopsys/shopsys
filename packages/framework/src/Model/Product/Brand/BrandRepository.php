<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;

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
    protected function getBrandRepository()
    {
        return $this->em->getRepository(Brand::class);
    }

    /**
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getById($brandId)
    {
        $brand = $this->getBrandRepository()->find($brandId);

        if ($brand === null) {
            $message = 'Brand with ID ' . $brandId . ' not found.';

            throw new BrandNotFoundException($message);
        }

        return $brand;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAll()
    {
        return $this->getBrandRepository()->findBy([], ['name' => 'asc']);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getOneByUuid(string $uuid): Brand
    {
        $brand = $this->getBrandRepository()->findOneBy(['uuid' => $uuid]);

        if ($brand === null) {
            throw new BrandNotFoundException('Brand with UUID ' . $uuid . ' does not exist.');
        }

        return $brand;
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getByUuids(array $uuids): array
    {
        return $this->getBrandRepository()->findBy(['uuid' => $uuids]);
    }

    /**
     * @param int[] $brandsIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsByIds(array $brandsIds): array
    {
        $brandsQueryBuilder = $this->getBrandRepository()->createQueryBuilder('b')
            ->select('b')
            ->where('b.id IN (:brandIds)')
            ->setParameter('brandIds', $brandsIds)
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('b.name', $this->domain->getLocale()), 'asc');

        return $brandsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsBySearchText(string $searchText): array
    {
        $queryBuilder = $this->getBrandRepository()
            ->createQueryBuilder('b')
            ->andWhere(
                'NORMALIZED(b.name) LIKE NORMALIZED(:searchText)',
            );
        $queryBuilder->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($searchText));
        $queryBuilder->orderBy(OrderByCollationHelper::createOrderByForLocale('b.name', $this->domain->getLocale()));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAllWithDomainsAndTranslations(DomainConfig $domainConfig): array
    {
        return $this->getAllWithDomainsAndTranslationsQueryBuilder($domainConfig)
            ->orderBy('b.name')
            ->getQuery()->getResult();
    }

    /**
     * @param int[] $brandIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null>
     */
    public function getByIds(array $brandIds, DomainConfig $domainConfig): array
    {
        $result = $this->getAllWithDomainsAndTranslationsQueryBuilder($domainConfig)
            ->andWhere('b.id IN (:brandIds)')
            ->indexBy('b', 'b.id')
            ->setParameter('brandIds', $brandIds)
            ->getQuery()->getResult();

        $brands = [];

        foreach ($brandIds as $brandId) {
            if (isset($result[$brandId])) {
                $brands[] = $result[$brandId];
            } else {
                $brands[] = null;
            }
        }

        return $brands;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllWithDomainsAndTranslationsQueryBuilder(DomainConfig $domainConfig): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('b, bd, bt')
            ->from(Brand::class, 'b')
            ->join('b.domains', 'bd', Join::WITH, 'bd.domainId = :domainId')
            ->join('b.translations', 'bt', Join::WITH, 'bt.locale = :locale')
            ->setParameter('domainId', $domainConfig->getId())
            ->setParameter('locale', $domainConfig->getLocale());
    }
}
