<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FlagFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository $flagRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFactory $flagFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FlagRepository $flagRepository,
        protected readonly FlagFactory $flagFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param int $flagId
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function getById($flagId)
    {
        return $this->flagRepository->getById($flagId);
    }

    /**
     * @param int[] $flagIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getByIds(array $flagIds): array
    {
        return $this->flagRepository->getByIds($flagIds);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function getByUuid(string $uuid): Flag
    {
        return $this->flagRepository->getByUuid($uuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function create(FlagData $flagData)
    {
        $flag = $this->flagFactory->create($flagData);
        $this->em->persist($flag);
        $this->em->flush();

        $this->dispatchFlagEvent($flag, FlagEvent::CREATE);

        return $flag;
    }

    /**
     * @param int $flagId
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function edit($flagId, FlagData $flagData)
    {
        $flag = $this->flagRepository->getById($flagId);
        $flag->edit($flagData);
        $this->em->flush();

        $this->dispatchFlagEvent($flag, FlagEvent::UPDATE);

        return $flag;
    }

    /**
     * @param int $flagId
     */
    public function deleteById($flagId)
    {
        $flag = $this->flagRepository->getById($flagId);

        $this->em->remove($flag);

        $this->dispatchFlagEvent($flag, FlagEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAll()
    {
        return $this->flagRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Flag\FlagEvent class
     */
    protected function dispatchFlagEvent(Flag $flag, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new FlagEvent($flag), $eventType);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getByUuids(array $uuids): array
    {
        return $this->flagRepository->getByUuids($uuids);
    }

    /**
     * @param int[] $flagsIds
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getVisibleFlagsByIds(array $flagsIds, string $locale): array
    {
        return $this->flagRepository->getVisibleFlagsByIds($flagsIds, $locale);
    }

    /**
     * @param string[] $flagUuids
     * @return int[]
     */
    public function getFlagIdsByUuids(array $flagUuids): array
    {
        return $this->flagRepository->getFlagIdsByUuids($flagUuids);
    }
}
