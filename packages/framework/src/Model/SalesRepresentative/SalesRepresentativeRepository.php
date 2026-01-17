<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\Exception\SalesRepresentativeNotFoundException;

class SalesRepresentativeRepository
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    protected function getSalesRepresentativeRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(SalesRepresentative::class);
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getSalesRepresentativeRepository()->createQueryBuilder('sr');
    }

    public function getById(int $id): SalesRepresentative
    {
        $salesRepresentative = $this->getSalesRepresentativeRepository()->find($id);

        if ($salesRepresentative === null) {
            throw new SalesRepresentativeNotFoundException('Sales representative with id `' . $id . '` not found.');
        }

        return $salesRepresentative;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative[]
     */
    public function getAll(): array
    {
        return $this->getAllQueryBuilder()->getQuery()->getResult();
    }
}
