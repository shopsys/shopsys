<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\PriceList\Exception\PriceListNotFoundException;

class PriceListRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getPriceListRepository(): EntityRepository
    {
        return $this->em->getRepository(PriceList::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceList
     */
    public function getById(int $id): PriceList
    {
        $priceList = $this->getPriceListRepository()->find($id);

        if ($priceList === null) {
            throw new PriceListNotFoundException('Price list with ID "' . $id . '" not found.');
        }

        return $priceList;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPriceListGridQueryBuilder(): QueryBuilder
    {
        return $this->getPriceListRepository()
            ->createQueryBuilder('pl')
            ->addSelect('pl.name');
    }
}
