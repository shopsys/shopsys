<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Feed;

use Monolog\Handler\NullHandler;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Feed\DailyFeedCronModule;
use Shopsys\FrameworkBundle\Model\Feed\FeedExport;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Symfony\Bridge\Monolog\Logger;
use Tests\FrameworkBundle\Unit\TestCase;

class DailyFeedCronModuleTest extends TestCase
{
    public function testSleepExactBetweenFeeds(): void
    {
        $feedInfoMock = $this->getMockForAbstractClass(FeedInfoInterface::class);

        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $domainConfig = new DomainConfig(1, 'http://example.com', 'name', 'en');
        $domain = new Domain([$domainConfig], $settingMock);

        $feedExportMock = $this->getMockBuilder(FeedExport::class)
            ->disableOriginalConstructor()
            ->setMethods(['isFinished', 'generateBatch', 'getFeedInfo', 'getDomainConfig', 'sleep'])
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
            ->setMethods(['getFeedNames', 'createFeedExport', 'getFeedFilepath'])
            ->getMock();
        $feedFacadeMock->expects($this->any())->method('getFeedNames')->willReturn(['feed1', 'feed2']);
        $feedFacadeMock->expects($this->any())->method('createFeedExport')->willReturn($feedExportMock);
        $feedFacadeMock->expects($this->any())->method('getFeedFilepath')->willReturn('path');

        $dailyFeedCronModule = new DailyFeedCronModule($feedFacadeMock, $domain, $settingMock);
        $dailyFeedCronModule->setLogger($logger);

        $dailyFeedCronModule->iterate();
        $dailyFeedCronModule->sleep();
    }
}
