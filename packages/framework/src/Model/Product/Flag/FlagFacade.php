<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FlagFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FlagRepository $flagRepository,
        protected readonly FlagFactory $flagFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    public function getById(int $flagId): Flag
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

    public function getByUuid(string $uuid): Flag
    {
        return $this->flagRepository->getByUuid($uuid);
    }

    public function create(FlagData $flagData): Flag
    {
        $flag = $this->flagFactory->create($flagData);
        $this->em->persist($flag);
        $this->em->flush();

        $this->dispatchFlagEvent($flag, FlagEvent::CREATE);

        $this->friendlyUrlFacade->createFriendlyUrls('front_flag_detail', $flag->getId(), $flag->getNames());

        return $flag;
    }

    public function edit(int $flagId, FlagData $flagData): Flag
    {
        $flag = $this->flagRepository->getById($flagId);
        $flag->edit($flagData);
        $this->em->flush();

        $this->dispatchFlagEvent($flag, FlagEvent::UPDATE);

        $this->friendlyUrlFacade->saveUrlListFormData('front_flag_detail', $flag->getId(), $flagData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_flag_detail', $flag->getId(), $flag->getNames());

        return $flag;
    }

    public function deleteById(int $flagId): void
    {
        $flag = $this->flagRepository->getById($flagId);

        $this->em->remove($flag);

        $this->dispatchFlagEvent($flag, FlagEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAll(): array
    {
        return $this->flagRepository->getAll();
    }

    /**
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAllVisibleFlags(string $locale): array
    {
        return $this->flagRepository->getAllVisibleFlags($locale);
    }

    public function getVisibleByUuid(string $uuid, string $locale): Flag
    {
        return $this->flagRepository->getVisibleByUuid($uuid, $locale);
    }

    public function getVisibleFlagById(int $flagId, string $locale): Flag
    {
        return $this->flagRepository->getVisibleFlagById($flagId, $locale);
    }

    public function getFlagDependencies(int $flagId): FlagDependenciesData
    {
        return $this->flagRepository->getFlagDependencies($flagId);
    }

    /**
     * @return int[]
     */
    public function getFlagsIdsWithPromotionXy(): array
    {
        return $this->flagRepository->getFlagsIdsWithPromotionXy();
    }
}
