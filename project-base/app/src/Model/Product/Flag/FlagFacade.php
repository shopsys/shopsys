<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade as BaseFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \App\Model\Product\Flag\FlagRepository $flagRepository
 * @method \App\Model\Product\Flag\Flag getById(int $flagId)
 * @method \App\Model\Product\Flag\Flag[] getAll()
 * @method dispatchFlagEvent(\App\Model\Product\Flag\Flag $flag, string $eventType)
 * @method \App\Model\Product\Flag\Flag[] getByIds(int[] $flagIds)
 * @method \App\Model\Product\Flag\Flag getByUuid(string $uuid)
 * @method \App\Model\Product\Flag\Flag[] getByUuids(string[] $uuids)
 * @method \App\Model\Product\Flag\Flag[] getVisibleFlagsByIds(int[] $flagsIds, string $locale)
 * @method \App\Model\Product\Flag\Flag[] getAllVisibleFlags(string $locale)
 * @method \App\Model\Product\Flag\Flag getVisibleByUuid(string $uuid, string $locale)
 * @method \App\Model\Product\Flag\Flag getVisibleFlagById(int $flagId, string $locale)
 */
class FlagFacade extends BaseFlagFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Flag\FlagRepository $flagRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFactory $flagFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        FlagRepository $flagRepository,
        FlagFactory $flagFactory,
        EventDispatcherInterface $eventDispatcher,
        private FriendlyUrlFacade $friendlyUrlFacade,
    ) {
        parent::__construct($em, $flagRepository, $flagFactory, $eventDispatcher);
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     * @return \App\Model\Product\Flag\Flag
     */
    public function create(FlagData $flagData)
    {
        /** @var \App\Model\Product\Flag\Flag $flag */
        $flag = parent::create($flagData);

        $this->friendlyUrlFacade->createFriendlyUrls('front_flag_detail', $flag->getId(), $flag->getNames());

        return $flag;
    }

    /**
     * @param int $flagId
     * @param \App\Model\Product\Flag\FlagData $flagData
     * @return \App\Model\Product\Flag\Flag
     */
    public function edit($flagId, FlagData $flagData)
    {
        /** @var \App\Model\Product\Flag\Flag $flag */
        $flag = parent::edit($flagId, $flagData);

        $this->friendlyUrlFacade->saveUrlListFormData('front_flag_detail', $flag->getId(), $flagData->urls);
        $this->friendlyUrlFacade->createFriendlyUrls('front_flag_detail', $flag->getId(), $flag->getNames());

        return $flag;
    }

    /**
     * @param int $flagId
     * @return \App\Model\Product\Flag\FlagDependenciesData
     */
    public function getFlagDependencies(int $flagId): FlagDependenciesData
    {
        return $this->flagRepository->getFlagDependencies($flagId);
    }
}
