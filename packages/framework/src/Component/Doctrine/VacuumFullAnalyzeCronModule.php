<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Component\Cron\CronControlFacade;
use Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Component\HttpClient\Exception\TimeoutException;

class VacuumFullAnalyzeCronModule implements SimpleCronModuleInterface
{
    protected const int ONE_HOUR_IN_SECONDS = 3600;

    protected const string VACUUM_CRON_INSTANCE_NAME = 'vacuum';

    protected Logger $logger;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronControlFacade $cronControlFacade
     * @param \Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeFacade $maintenanceModeFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly CronControlFacade $cronControlFacade,
        protected readonly MaintenanceModeFacade $maintenanceModeFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function run(): void
    {
        if (!$this->cronControlFacade->lockCron()) {
            $this->logger->error('Cron locking failed.');

            return;
        }

        $this->logger->info('Cron is now locked.');

        try {
            $this->logger->info('Waiting till all the cron instances are finished.');
            $this->cronControlFacade->waitUntilCronInstancesAreFinished(
                [self::VACUUM_CRON_INSTANCE_NAME],
                self::ONE_HOUR_IN_SECONDS,
            );
            $this->logger->info('Turning on maintenance page');
            $this->maintenanceModeFacade->enable();
            $this->makeVacuum();
        } catch (TimeoutException) {
            $this->logger->error(sprintf('The vacuum command was not executed because it was not possible to stop all cron modules within the time limit of %d seconds.', static::ONE_HOUR_IN_SECONDS));
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } finally {
            $this->logger->info('Turning off maintenance page');
            $this->maintenanceModeFacade->disable();
            $this->cronControlFacade->unlockCron();
            $this->logger->info('Cron lock was released.');
        }
    }

    protected function makeVacuum(): void
    {
        $this->logger->info('Start of database vacuum');
        $this->entityManager->getConnection()->executeQuery('VACUUM FULL ANALYSE');
        $this->logger->info('End of database vacuum');
    }
}
