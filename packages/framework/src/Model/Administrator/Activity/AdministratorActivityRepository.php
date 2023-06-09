<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\Exception\CurrentAdministratorActivityNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdministratorActivityRepository()
    {
        return $this->em->getRepository(AdministratorActivity::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param int $maxResults
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getLastActivitiesQueryBuilder(Administrator $administrator, $maxResults)
    {
        $lastActivitiesQueryBuilder = $this->getAdministratorActivityRepository()->createQueryBuilder('aa');

        $lastActivitiesQueryBuilder
            ->where('aa.administrator = :administrator')->setParameter('administrator', $administrator)
            ->orderBy('aa.loginTime', 'DESC')
            ->setMaxResults($maxResults);

        return $lastActivitiesQueryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity
     */
    public function getCurrent(Administrator $administrator)
    {
        $currentAdministratorActivity = $this->getLastActivitiesQueryBuilder(
            $administrator,
            1,
        )->getQuery()->getSingleResult();
        if ($currentAdministratorActivity === null) {
            throw new CurrentAdministratorActivityNotFoundException();
        }

        return $currentAdministratorActivity;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param int $maxResults
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity[]
     */
    public function getLastAdministratorActivities(Administrator $administrator, $maxResults)
    {
        $lastActivitiesQueryBuilder = $this->getLastActivitiesQueryBuilder($administrator, $maxResults);

        return $lastActivitiesQueryBuilder->getQuery()->getResult();
    }
}
