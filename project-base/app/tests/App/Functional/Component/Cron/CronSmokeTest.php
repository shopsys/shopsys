<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Cron;

use Monolog\Logger;
use ReflectionProperty;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleExecutor;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\TestTranslatableListener;
use Throwable;

final class CronSmokeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CronFacade $cronFacade;

    /**
     * @inject
     */
    private CronModuleExecutor $cronModuleExecutor;

    /**
     * @inject
     */
    private FeedFacade $feedFacade;

    /**
     * @inject monolog.logger.cron
     */
    private Logger $cronLogger;

    /**
     * @inject prezent_doctrine_translatable.listener
     */
    private TestTranslatableListener $translatableListener;

    public function testCronModulesRunWithoutCurrentDomainAndLocale(): void
    {
        $this->feedFacade->scheduleAllFeeds();
        $this->simulateConsoleProcess();

        $failures = [];

        foreach ($this->cronFacade->getAll() as $cronModuleConfig) {
            if (array_key_exists($cronModuleConfig->getServiceId(), $this->getExcludedCronModules())) {
                continue;
            }

            $failure = $this->findCronModuleFailure($cronModuleConfig);

            if ($failure !== null) {
                $failures[] = $cronModuleConfig->getServiceId() . ': ' . $failure;
            }
        }

        $this->assertCount(0, $failures, implode("\n\n", $failures));
    }

    /**
     * Exclude a cron module only when it cannot run in a test, e.g. its external service cannot be replaced.
     *
     * @return array<class-string, string> reason for the exclusion indexed by the cron module class
     */
    private function getExcludedCronModules(): array
    {
        return [];
    }

    private function findCronModuleFailure(CronModuleConfig $cronModuleConfig): ?string
    {
        $cronModule = $cronModuleConfig->getService();
        $cronModule->setLogger($this->cronLogger);

        try {
            $status = $this->cronModuleExecutor->runModule($cronModule, false);
        } catch (Throwable $throwable) {
            return (string)$throwable;
        }

        return $status === CronModuleExecutor::RUN_STATUS_OK ? null : 'finished with status "' . $status . '"';
    }

    /**
     * Cron runs in a console process without a selected domain and a current locale, while the test environment
     * provides both. Entities are detached because the locale is assigned to them when they are loaded.
     */
    private function simulateConsoleProcess(): void
    {
        $currentDomainConfig = new ReflectionProperty(Domain::class, 'currentDomainConfig');
        $currentDomainConfig->setValue($this->domain, null);

        $this->translatableListener->disableImplicitLocale();
        $this->em->clear();
    }
}
