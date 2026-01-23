<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Search\SearchSetting;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;

class BrandRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getBrandRepository(): EntityRepository
    {
        return $this->em->getRepository(Brand::class);
    }

    public function getById(int $brandId): Brand
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
    public function getAll(): array
    {
        return $this->getBrandRepository()->findBy([], ['name' => 'asc']);
    }

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
        $brandsQueryBuilder = $this->getBrandsByIdsQueryBuilder($brandsIds)
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('b.name', $this->domain->getLocale()), 'asc');

        return $brandsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param int[] $brandIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsByIdsPreservingInputOrder(array $brandIds): array
    {
        $brandsQueryBuilder = $this->getBrandsByIdsQueryBuilder($brandIds)
            ->addSelect('field(b.id, ' . implode(',', $brandIds) . ') AS HIDDEN relevance')
            ->orderBy('relevance');

        return $brandsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsBySearchText(string $searchText): array
    {
        $queryBuilder = $this->getBrandRepository()
            ->createQueryBuilder('b');

        $this->addDomain($queryBuilder, $this->domain->getId());

        $queryBuilder->andWhere(
            'NORMALIZED(b.name) LIKE NORMALIZED(:searchText) OR NORMALIZED(bd.seoH1) LIKE NORMALIZED(:searchText) OR NORMALIZED(bd.seoTitle) LIKE NORMALIZED(:searchText) OR NORMALIZED(bd.seoMetaDescription) LIKE NORMALIZED(:searchText)',
        );

        if (mb_strlen($searchText) < SearchSetting::SIMPLE_SEARCH_THRESHOLD) {
            $queryBuilder->setParameter('searchText', $searchText . '%');
        } else {
            $queryBuilder->setParameter('searchText', $this->databaseSearchingHelper->getFullTextLikeSearchString($searchText));
        }

        $queryBuilder->orderBy($this->orderByCollationHelper->createOrderByForLocale('b.name', $this->domain->getLocale()));

        return $queryBuilder->getQuery()->getResult();
    }

    public function addDomain(QueryBuilder $brandsQueryBuilder, int $domainId): void
    {
        $brandsQueryBuilder
            ->addSelect('bd')
            ->join('b.domains', 'bd', Join::WITH, 'bd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
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

    /**
     * @param int[] $brandIds
     */
    protected function getBrandsByIdsQueryBuilder(array $brandIds): QueryBuilder
    {
        return $this->getBrandRepository()->createQueryBuilder('b')
            ->select('b')
            ->where('b.id IN (:brandIds)')
            ->setParameter('brandIds', $brandIds);
    }
}
