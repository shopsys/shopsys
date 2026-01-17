<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class SalesRepresentativeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        protected readonly SalesRepresentativeRepository $salesRepresentativeRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly SalesRepresentativeFactory $salesRepresentativeFactory,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->salesRepresentativeRepository->getAllQueryBuilder();
    }

    public function create(SalesRepresentativeData $salesRepresentativeData): SalesRepresentative
    {
        $salesRepresentative = $this->salesRepresentativeFactory->create($salesRepresentativeData);

        $this->entityManager->persist($salesRepresentative);
        $this->entityManager->flush();

        $this->imageFacade->manageImages($salesRepresentative, $salesRepresentativeData->image);

        return $salesRepresentative;
    }

    public function getById(int $id): SalesRepresentative
    {
        return $this->salesRepresentativeRepository->getById($id);
    }

    public function edit(
        SalesRepresentative $salesRepresentative,
        SalesRepresentativeData $salesRepresentativeData,
    ): void {
        $salesRepresentative->edit($salesRepresentativeData);
        $this->entityManager->flush();

        $this->imageFacade->manageImages($salesRepresentative, $salesRepresentativeData->image);
    }

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
}
