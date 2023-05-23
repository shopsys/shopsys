<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class CronModuleRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModuleFactoryInterface $cronModuleFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CronModuleFactoryInterface $cronModuleFactory,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCronModuleRepository()
    {
        return $this->em->getRepository(CronModule::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCronModuleRunRepository()
    {
        return $this->em->getRepository(CronModuleRun::class);
    }

    /**
     * @param string $serviceId
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule
     */
    public function getCronModuleByServiceId($serviceId)
    {
        $cronModule = $this->getCronModuleRepository()->find($serviceId);

        if ($cronModule === null) {
            $cronModule = $this->cronModuleFactory->create($serviceId);
            $this->em->persist($cronModule);
            $this->em->flush();
        }

        return $cronModule;
    }

    /**
     * @return string[]
     */
    public function getAllScheduledCronModuleServiceIds()
    {
        $query = $this->em->createQuery(
            'SELECT cm.serviceId FROM ' . CronModule::class . ' cm WHERE cm.scheduled = TRUE',
        );

        return $query->getSingleColumnResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule[]
     */
    public function findAllIndexedByServiceId(): array
    {
        return $this->getCronModuleRepository()->createQueryBuilder('cm')
            ->indexBy('cm', 'cm.serviceId')
            ->getQuery()->getResult();
    }

    /**
     * @return array<string, array{cronModuleId: string, minimalDuration: string, maximalDuration: string, averageDuration: string}>
     */
    public function getCronCalculatedDurationsIndexedByServiceId(): array
    {
        $cronModuleRunTimes = $this->getCronModuleRunRepository()->createQueryBuilder('cmr')
            ->select('IDENTITY(cmr.cronModule) as cronModuleId, MIN(cmr.duration) AS minimalDuration, MAX(cmr.duration) AS maximalDuration, AVG(cmr.duration) AS averageDuration')
            ->groupBy('cmr.cronModule')
            ->getQuery()->getResult();

        $cronModuleRunTimesIndexedByCronModuleId = [];

        foreach ($cronModuleRunTimes as $cronModuleRunTime) {
            $cronModuleRunTimesIndexedByCronModuleId[$cronModuleRunTime['cronModuleId']] = $cronModuleRunTime;
        }

        return $cronModuleRunTimesIndexedByCronModuleId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule $cronModule
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleRun[]
     */
    public function getAllRunsByCronModule(CronModule $cronModule): array
    {
        return $this->getCronModuleRunRepository()->createQueryBuilder('cmr')
            ->where('cmr.cronModule = :cronModule')
            ->setParameter('cronModule', $cronModule)
            ->orderBy('cmr.startedAt', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule $cronModule
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRunsByCronModuleQueryBuilder(CronModule $cronModule): QueryBuilder
    {
        return $this->getCronModuleRunRepository()->createQueryBuilder('cmr')
            ->addSelect('cmr.duration, cmr.status')
            ->where('cmr.cronModule = :cronModule')
            ->setParameter('cronModule', $cronModule)
            ->orderBy('cmr.startedAt', 'DESC');
    }

    /**
     * @param int $numberOfDays
     */
    public function deleteOldCronModuleRuns(int $numberOfDays): void
    {
        $this->em->getConnection()->executeStatement(
            'DELETE FROM cron_module_runs WHERE finished_at <= :timeLimit',
            [
                'timeLimit' => new DateTime('-' . $numberOfDays . ' days'),
            ],
            [
                'timeLimit' => Types::DATETIME_MUTABLE,
            ],
        );
    }
}
