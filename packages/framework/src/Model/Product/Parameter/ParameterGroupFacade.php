<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParameterGroupFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterGroupFactory $parameterGroupFactory,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(ParameterGroupData $parameterGroupData): ParameterGroup
    {
        $parameterGroup = $this->parameterGroupFactory->create($parameterGroupData);
        $this->em->persist($parameterGroup);
        $this->em->flush();

        $this->dispatchParameterGroupEvent($parameterGroup, ParameterGroupEvent::CREATE);

        return $parameterGroup;
    }

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

    public function getOrderedParameterGroupsQueryBuilder(
        string $locale,
    ): QueryBuilder {
        return $this->parameterRepository->getOrderedParameterGroupsQueryBuilder($locale);
    }

    public function getById(int $parameterGroupId): ParameterGroup
    {
        return $this->parameterRepository->getParameterGroupById($parameterGroupId);
    }

    public function existsParameterGroupByName(
        string $name,
        string $locale,
        ?ParameterGroup $excludeParameterGroup = null,
    ): bool {
        return $this->parameterRepository->existsParameterGroupByName($name, $locale, $excludeParameterGroup);
    }

    public function deleteById(int $parameterGroupId): void
    {
        $parameterGroup = $this->parameterRepository->getParameterGroupById($parameterGroupId);

        $this->em->remove($parameterGroup);

        $this->dispatchParameterGroupEvent($parameterGroup, ParameterGroupEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @see \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent class
     */
    protected function dispatchParameterGroupEvent(ParameterGroup $parameterGroup, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new ParameterGroupEvent($parameterGroup), $eventType);
    }
}
