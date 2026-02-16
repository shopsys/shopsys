<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Feed;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Feed\FeedCronModule;
use Shopsys\FrameworkBundle\Model\Feed\FeedExport;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedModule;
use Shopsys\FrameworkBundle\Model\Feed\FeedModuleFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Unit\TestCase;

class FeedCronModuleTest extends TestCase
{
    public function testSleepExactBetweenFeeds(): void
    {
        $feedInfoStub = $this->createStub(FeedInfoInterface::class);

        $settingStub = $this->createStub(Setting::class);

        $feedModuleRepositoryStub = $this->createStub(FeedModuleRepository::class);

        $feedModule1 = new FeedModule('feed1', 1);
        $feedModule2 = new FeedModule('feed2', 1);

        $feedModuleRepositoryStub->method('getAllScheduledFeedModules')->willReturn([$feedModule1, $feedModule2]);
        $feedModuleRepositoryStub->method('getFeedModuleByNameAndDomainId')->willReturn($feedModule1);

        $domainConfig = DomainConfigHelper::getDomainConfig();
        $currentAdministratorStub = $this->createStub(CurrentAdministrator::class);

        $domain = new Domain(
            [$domainConfig],
            $settingStub,
            $currentAdministratorStub,
        );

        $feedExportMock = $this->getMockBuilder(FeedExport::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isFinished', 'generateBatch', 'getFeedInfo', 'getDomainConfig', 'sleep'])
            ->getMock();
        $feedExportMock->expects($this->atLeastOnce())->method('isFinished')->willReturn(true);
        $feedExportMock->expects($this->any())->method('generateBatch');
        $feedExportMock->expects($this->any())->method('getFeedInfo')->willReturn($feedInfoStub);
        $feedExportMock->expects($this->any())->method('getDomainConfig')->willReturn($domainConfig);

        $this->setValueOfProtectedProperty($feedExportMock, 'lastSeekId', null);

        $logger = new Logger('loggerName');
        $logger->setHandlers([new NullHandler()]);

        $feedFacadeStub = $this->createStub(FeedFacade::class);
        $feedFacadeStub->method('getFeedNames')->willReturn(['feed1', 'feed2']);
        $feedFacadeStub->method('createFeedExport')->willReturn($feedExportMock);
        $feedFacadeStub->method('getFeedFilepath')->willReturn('path');
        $feedFacadeStub->method('scheduleFeedsForCurrentTime');
        $feedFacadeStub->method('markFeedModuleAsUnscheduled');

        $feedModuleFacadeStub = $this->createStub(FeedModuleFacade::class);

        $feedCronModule = new FeedCronModule($feedFacadeStub, $domain, $settingStub, $feedModuleRepositoryStub, $feedModuleFacadeStub);
        $feedCronModule->setLogger($logger);

        $feedCronModule->iterate();
        $feedCronModule->sleep();
    }
}
