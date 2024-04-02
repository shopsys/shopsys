<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class TransportTypeFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeRepository $transportTypeRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected TransportTypeRepository $transportTypeRepository,
        protected EntityManagerInterface $em,
    ) {
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLocalisedQueryBuilder(string $locale): QueryBuilder
    {
        return $this->transportTypeRepository->getLocalisedQueryBuilder($locale);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType
     */
    public function getById(int $id): TransportType
    {
        return $this->transportTypeRepository->getById($id);
    }

    /**
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType
     */
    public function getByCode(string $code): TransportType
    {
        return $this->transportTypeRepository->getByCode($code);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType[]
     */
    public function getAll(): array
    {
        return $this->transportTypeRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType $transportType
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType
     */
    public function edit(TransportType $transportType, TransportTypeData $transportTypeData): TransportType
    {
        $transportType->edit($transportTypeData);

        $this->em->flush();

        return $transportType;
    }
}
