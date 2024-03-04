<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
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
                'NORMALIZE(b.name) LIKE NORMALIZE(:searchText)',
            );
        $queryBuilder->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($searchText));
        $queryBuilder->orderBy(OrderByCollationHelper::createOrderByForLocale('b.name', $this->domain->getLocale()));

        return $queryBuilder->getQuery()->getResult();
    }
}
