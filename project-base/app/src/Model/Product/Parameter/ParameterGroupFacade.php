<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;

class ParameterGroupFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterGroupFactory $parameterGroupFactory
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(
        private EntityManagerInterface $em,
        private ParameterGroupFactory $parameterGroupFactory,
        private ParameterRepository $parameterRepository,
    ) {
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @return \App\Model\Product\Parameter\ParameterGroup
     */
    public function create(ParameterGroupData $parameterGroupData): ParameterGroup
    {
        $parameterGroup = $this->parameterGroupFactory->create($parameterGroupData);
        $this->em->persist($parameterGroup);
        $this->em->flush();

        return $parameterGroup;
    }

    /**
     * @param string[] $names
     * @return \App\Model\Product\Parameter\ParameterGroup|null
     */
    public function findParameterGroupByNames(array $names): ?ParameterGroup
    {
        return $this->parameterRepository->findParameterGroupByNames($names);
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\ParameterGroup|null
     */
    public function findParameterGroupByAkeneoCode(string $akeneoCode): ?ParameterGroup
    {
        return $this->parameterRepository->findParameterGroupByAkeneoCode($akeneoCode);
    }

    /**
     * @param int $parameterGroupId
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @return \App\Model\Product\Parameter\ParameterGroup
     */
    public function edit(int $parameterGroupId, ParameterGroupData $parameterGroupData): ParameterGroup
    {
        $parameterGroup = $this->parameterRepository->getParameterGroupById($parameterGroupId);
        $parameterGroup->edit($parameterGroupData);

        $this->em->flush();

        return $parameterGroup;
    }
}
