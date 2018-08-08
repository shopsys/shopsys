<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getAdministratorActivityRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(AdministratorActivity::class);
    }

    /**
     * @param int $maxResults
     */
    protected function getLastActivitiesQueryBuilder(Administrator $administrator, $maxResults): \Doctrine\ORM\QueryBuilder
    {
        $lastActivitiesQueryBuilder = $this->getAdministratorActivityRepository()->createQueryBuilder('aa');

        $lastActivitiesQueryBuilder
            ->where('aa.administrator = :administrator')->setParameter('administrator', $administrator)
            ->orderBy('aa.loginTime', 'DESC')
            ->setMaxResults($maxResults);

        return $lastActivitiesQueryBuilder;
    }

    public function getCurrent(Administrator $administrator): \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity
    {
        $currentAdministratorActivity = $this->getLastActivitiesQueryBuilder($administrator, 1)->getQuery()->getSingleResult();
        if ($currentAdministratorActivity === null) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Activity\Exception\CurrentAdministratorActivityNotFoundException();
        }

        return $currentAdministratorActivity;
    }

    /**
     * @param int $maxResults
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity[]
     */
    public function getLastAdministratorActivities(Administrator $administrator, $maxResults): array
    {
        $lastActivitiesQueryBuilder = $this->getLastActivitiesQueryBuilder($administrator, $maxResults);

        return $lastActivitiesQueryBuilder->getQuery()->getResult();
    }
}
