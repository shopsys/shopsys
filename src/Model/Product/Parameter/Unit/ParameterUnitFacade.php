<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

use Doctrine\ORM\EntityManagerInterface;

class ParameterUnitFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitRepository
     */
    private $parameterUnitRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitRepository $parameterUnitRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterUnitRepository $parameterUnitRepository
    ) {
        $this->em = $em;
        $this->parameterUnitRepository = $parameterUnitRepository;
    }

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitData $parameterUnitData
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit
     */
    public function create(ParameterUnitData $parameterUnitData): ParameterUnit
    {
        $parameterUnit = new ParameterUnit($parameterUnitData);
        $this->em->persist($parameterUnit);
        $this->em->flush();

        return $parameterUnit;
    }

    /**
     * @param int $parameterUnitId
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitData $parameterUnitData
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit
     */
    public function edit(int $parameterUnitId, ParameterUnitData $parameterUnitData): ParameterUnit
    {
        $parameterUnit = $this->getById($parameterUnitId);
        $parameterUnit->edit($parameterUnitData);
        $this->em->flush();
        return $parameterUnit;
    }

    /**
     * @param int $unitId
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit
     */
    public function getById(int $unitId): ParameterUnit
    {
        return $this->parameterUnitRepository->getById($unitId);
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ParameterUnit
    {
        return $this->parameterUnitRepository->findByAkeneoCode($akeneoCode);
    }
}
