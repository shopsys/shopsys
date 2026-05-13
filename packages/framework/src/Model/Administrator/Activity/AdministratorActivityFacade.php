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

    public function create(
        Administrator $administrator,
        string $ipAddress,
    ): AdministratorActivity {
        $administratorActivity = $this->administratorActivityFactory->create($administrator, $ipAddress);

        $this->em->persist($administratorActivity);
        $this->em->flush();

        return $administratorActivity;
    }

    public function updateCurrentActivity(Administrator $administrator): void
    {
        $administrator->setLastActivity($this->clock->now());
        $currentAdministratorActivity = $this->administratorActivityRepository->getCurrent($administrator);
        $currentAdministratorActivity->updateLastActionTime();
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity[]
     */
    public function getLastAdministratorActivities(Administrator $administrator, int $maxResults): array
    {
        return $this->administratorActivityRepository->getLastAdministratorActivities($administrator, $maxResults);
    }
}
