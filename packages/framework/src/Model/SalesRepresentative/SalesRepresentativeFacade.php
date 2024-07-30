<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class SalesRepresentativeFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeRepository $salesRepresentativeRepository
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFactory $salesRepresentativeFactory
     */
    public function __construct(
        protected readonly SalesRepresentativeRepository $salesRepresentativeRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly SalesRepresentativeFactory $salesRepresentativeFactory,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->salesRepresentativeRepository->getAllQueryBuilder();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $salesRepresentativeData
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative
     */
    public function create(SalesRepresentativeData $salesRepresentativeData): SalesRepresentative
    {
        $salesRepresentative = $this->salesRepresentativeFactory->create($salesRepresentativeData);

        $this->entityManager->persist($salesRepresentative);
        $this->entityManager->flush();

        return $salesRepresentative;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative
     */
    public function getById(int $id): SalesRepresentative
    {
        return $this->salesRepresentativeRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative $salesRepresentative
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $salesRepresentativeData
     */
    public function edit(
        SalesRepresentative $salesRepresentative,
        SalesRepresentativeData $salesRepresentativeData,
    ): void {
        $salesRepresentative->edit($salesRepresentativeData);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $salesRepresentative = $this->salesRepresentativeRepository->getById($id);

        $this->entityManager->remove($salesRepresentative);
        $this->entityManager->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative[]
     */
    public function getAll(): array
    {
        return $this->salesRepresentativeRepository->getAll();
    }

    /**
     * @param int $id
     * @return int
     */
    public function findCustomersWithSalesRepresentative(int $id): int
    {
        return 0;
    }
}
