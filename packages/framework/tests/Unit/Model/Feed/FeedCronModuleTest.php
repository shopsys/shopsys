<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Feed;

use DateTimeZone;
use Monolog\Handler\NullHandler;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Feed\FeedCronModule;
use Shopsys\FrameworkBundle\Model\Feed\FeedExport;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedModule;
use Shopsys\FrameworkBundle\Model\Feed\FeedModuleFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository;
use Symfony\Bridge\Monolog\Logger;
use Tests\FrameworkBundle\Unit\TestCase;

class FeedCronModuleTest extends TestCase
{
    public function testSleepExactBetweenFeeds(): void
    {
        $feedInfoMock = $this->getMockBuilder(FeedInfoInterface::class)->getMock();

        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $feedModuleRepositoryMock = $this->getMockBuilder(FeedModuleRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllScheduledFeedModules', 'getFeedModuleByNameAndDomainId'])
            ->getMock();

        $feedModule1 = new FeedModule('feed1', 1);
        $feedModule2 = new FeedModule('feed2', 1);

        $feedModuleRepositoryMock->expects($this->any())->method('getAllScheduledFeedModules')->willReturn([$feedModule1, $feedModule2]);
        $feedModuleRepositoryMock->expects($this->any())->method('getFeedModuleByNameAndDomainId')->willReturn($feedModule1);

        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfig = new DomainConfig(1, 'http://example.com', 'name', 'en', $defaultTimeZone);
        $domain = new Domain([$domainConfig], $settingMock);

        $feedExportMock = $this->getMockBuilder(FeedExport::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isFinished', 'generateBatch', 'getFeedInfo', 'getDomainConfig', 'sleep'])
            ->getMock();
        $feedExportMock->expects($this->atLeastOnce())->method('isFinished')->willReturn(true);
        $feedExportMock->expects($this->any())->method('generateBatch');
        $feedExportMock->expects($this->any())->method('getFeedInfo')->willReturn($feedInfoMock);
        $feedExportMock->expects($this->any())->method('getDomainConfig')->willReturn($domainConfig);

        $this->setValueOfProtectedProperty($feedExportMock, 'lastSeekId', null);

        $logger = new Logger('loggerName');
        $logger->setHandlers([new NullHandler()]);

        $feedFacadeMock = $this->getMockBuilder(FeedFacade::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFeedNames', 'createFeedExport', 'getFeedFilepath', 'scheduleFeedsForCurrentTime', 'markFeedModuleAsUnscheduled'])
            ->getMock();
        $feedFacadeMock->expects($this->any())->method('getFeedNames')->willReturn(['feed1', 'feed2']);
        $feedFacadeMock->expects($this->any())->method('createFeedExport')->willReturn($feedExportMock);
        $feedFacadeMock->expects($this->any())->method('getFeedFilepath')->willReturn('path');
        $feedFacadeMock->expects($this->any())->method('scheduleFeedsForCurrentTime');
        $feedFacadeMock->expects($this->any())->method('markFeedModuleAsUnscheduled');

        $feedModuleFacadeMock = $this->getMockBuilder(FeedModuleFacade::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['deleteFeedCronModulesByName'])
            ->getMock();

        $feedCronModule = new FeedCronModule($feedFacadeMock, $domain, $settingMock, $feedModuleRepositoryMock, $feedModuleFacadeMock);
        $feedCronModule->setLogger($logger);

        $feedCronModule->iterate();
        $feedCronModule->sleep();
    }
}
