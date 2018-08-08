<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityRepository
     */
    protected $administratorActivityRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFactoryInterface
     */
    protected $administratorActivityFactory;

    public function __construct(
        EntityManagerInterface $em,
        AdministratorActivityRepository $administratorActivityRepository,
        AdministratorActivityFactoryInterface $administratorActivityFactory
    ) {
        $this->em = $em;
        $this->administratorActivityRepository = $administratorActivityRepository;
        $this->administratorActivityFactory = $administratorActivityFactory;
    }

    /**
     * @param string $ipAddress
     */
    public function create(
        Administrator $administrator,
        $ipAddress
    ): \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity {
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
    public function getLastAdministratorActivities(Administrator $administrator, $maxResults): array
    {
        return $this->administratorActivityRepository->getLastAdministratorActivities($administrator, $maxResults);
    }
}
