<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\PriceList\Exception\PriceListNotFoundException;

class PriceListRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getPriceListRepository(): EntityRepository
    {
        return $this->em->getRepository(PriceList::class);
    }

    public function getById(int $id): PriceList
    {
        $priceList = $this->getPriceListRepository()->find($id);

        if ($priceList === null) {
            throw new PriceListNotFoundException('Price list with ID "' . $id . '" not found.');
        }

        return $priceList;
    }

    public function getPriceListGridQueryBuilder(): QueryBuilder
    {
        return $this->getPriceListRepository()
            ->createQueryBuilder('pl')
            ->addSelect('CASE
                    WHEN :now BETWEEN pl.validFrom AND pl.validTo THEN 0
                    WHEN :now < pl.validFrom THEN 1
                    ELSE -1
                END AS validityStatus')
            ->setParameter('now', $this->clock->now());
    }

    public function getPriceListDataToExport(int $priceListId): iterable
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select([
                'p.catnum as ' . PriceListCsvColumnsEnum::PRODUCT_CATNUM,
                'plpp.priceAmount as ' . PriceListCsvColumnsEnum::PRICE,
            ])
            ->from(PriceListProductPrice::class, 'plpp')
            ->leftJoin('plpp.product', 'p')
            ->where('plpp.priceList = :priceListId')
            ->setParameter('priceListId', $priceListId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceList[]
     */
    public function getAll(): array
    {
        return $this->getPriceListRepository()->findAll();
    }
}
