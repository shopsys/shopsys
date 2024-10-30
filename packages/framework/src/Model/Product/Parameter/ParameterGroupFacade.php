<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParameterGroupFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFactory $parameterGroupFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterGroupFactory $parameterGroupFactory,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly ProductFacade $productFacade,
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

        $this->dispatchParameterGroupEvent($parameterGroup, ParameterGroupEvent::UPDATE);

        return $parameterGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup[]
     */
    public function getAll(): array
    {
        return $this->parameterRepository->getAllParameterGroups();
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedParameterGroupsQueryBuilder(
        string $locale,
    ): QueryBuilder {
        return $this->parameterRepository->getOrderedParameterGroupsQueryBuilder($locale);
    }

    /**
     * @param int $parameterGroupId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    public function getById(int $parameterGroupId)
    {
        return $this->parameterRepository->getParameterGroupById($parameterGroupId);
    }

    /**
     * @param string $name
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup|null $excludeParameterGroup
     * @return bool
     */
    public function existsParameterGroupByName(
        string $name,
        string $locale,
        ?ParameterGroup $excludeParameterGroup = null,
    ): bool {
        return $this->parameterRepository->existsParameterGroupByName($name, $locale, $excludeParameterGroup);
    }

    /**
     * @param int $parameterGroupId
     */
    public function deleteById($parameterGroupId)
    {
        $parameterGroup = $this->parameterRepository->getParameterGroupById($parameterGroupId);

        $this->em->remove($parameterGroup);

        $this->dispatchParameterGroupEvent($parameterGroup, ParameterGroupEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup $parameterGroup
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent class
     */
    protected function dispatchParameterGroupEvent(ParameterGroup $parameterGroup, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new ParameterGroupEvent($parameterGroup), $eventType);
    }
}
