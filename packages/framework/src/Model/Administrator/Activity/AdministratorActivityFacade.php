<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityRepository $administratorActivityRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFactoryInterface $administratorActivityFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorActivityRepository $administratorActivityRepository,
        protected readonly AdministratorActivityFactoryInterface $administratorActivityFactory
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $ipAddress
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity
     */
    public function create(
        Administrator $administrator,
        $ipAddress
    ) {
        $administratorActivity = $this->administratorActivityFactory->create($administrator, $ipAddress);

        $this->em->persist($administratorActivity);
        $this->em->flush();

        return $administratorActivity;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function updateCurrentActivityLastActionTime(Administrator $administrator)
    {
        $currentAdministratorActivity = $this->administratorActivityRepository->getCurrent($administrator);
        $currentAdministratorActivity->updateLastActionTime();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param int $maxResults
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity[]
     */
    public function getLastAdministratorActivities(Administrator $administrator, $maxResults)
    {
        return $this->administratorActivityRepository->getLastAdministratorActivities($administrator, $maxResults);
    }
}
