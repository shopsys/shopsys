<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\Exception\CurrentAdministratorActivityNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getAdministratorActivityRepository(): EntityRepository
    {
        return $this->em->getRepository(AdministratorActivity::class);
    }

    protected function getLastActivitiesQueryBuilder(
        Administrator $administrator,
        int $maxResults,
    ): QueryBuilder {
        $lastActivitiesQueryBuilder = $this->getAdministratorActivityRepository()->createQueryBuilder('aa');

        $lastActivitiesQueryBuilder
            ->where('aa.administrator = :administrator')->setParameter('administrator', $administrator)
            ->orderBy('aa.loginTime', 'DESC')
            ->setMaxResults($maxResults);

        return $lastActivitiesQueryBuilder;
    }

    public function getCurrent(
        Administrator $administrator,
    ): AdministratorActivity {
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
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity[]
     */
    public function getLastAdministratorActivities(Administrator $administrator, int $maxResults): array
    {
        $lastActivitiesQueryBuilder = $this->getLastActivitiesQueryBuilder($administrator, $maxResults);

        return $lastActivitiesQueryBuilder->getQuery()->getResult();
    }
}
