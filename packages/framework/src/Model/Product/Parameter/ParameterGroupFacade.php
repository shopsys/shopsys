<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;

class ParameterGroupFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFactoryInterface $parameterGroupFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterGroupFactoryInterface $parameterGroupFactory,
        protected readonly ParameterRepository $parameterRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    public function create(ParameterGroupData $parameterGroupData): ParameterGroup
    {
        $parameterGroup = $this->parameterGroupFactory->create($parameterGroupData);
        $this->em->persist($parameterGroup);
        $this->em->flush();

        return $parameterGroup;
    }

    /**
     * @param int $parameterGroupId
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    public function edit(int $parameterGroupId, ParameterGroupData $parameterGroupData): ParameterGroup
    {
        $parameterGroup = $this->parameterRepository->getParameterGroupById($parameterGroupId);
        $parameterGroup->edit($parameterGroupData);

        $this->em->flush();

        return $parameterGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup[]
     */
    public function getAll(): array
    {
        return $this->parameterRepository->getAllParameterGroups();
    }
}
