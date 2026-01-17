<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorActivityRepository $administratorActivityRepository,
        protected readonly AdministratorActivityFactory $administratorActivityFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $ipAddress
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity
     */
    public function create(
        Administrator $administrator,
        $ipAddress,
    ) {
        $administratorActivity = $this->administratorActivityFactory->create($administrator, $ipAddress);

        $this->em->persist($administratorActivity);
        $this->em->flush();

        return $administratorActivity;
    }

    public function updateCurrentActivityLastActionTime(Administrator $administrator)
    {
        $currentAdministratorActivity = $this->administratorActivityRepository->getCurrent($administrator);
        $currentAdministratorActivity->updateLastActionTime();
        $this->em->flush();
    }

    /**
     * @param int $maxResults
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity[]
     */
    public function getLastAdministratorActivities(Administrator $administrator, $maxResults)
    {
        return $this->administratorActivityRepository->getLastAdministratorActivities($administrator, $maxResults);
    }
}
