<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Mutex;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\CronControlFacade;
use Shopsys\FrameworkBundle\Component\Cron\CronFacade;
use Shopsys\FrameworkBundle\Component\Cron\MutexFactory;

final class CronControlFacadeTest extends TestCase
{
    public function testWaitUntilCronInstancesAreFinishedSkipsExcludedInstances(): void
    {
        $cronFacadeStub = $this->createStub(CronFacade::class);
        $cronFacadeStub->method('getInstanceNames')->willReturn(['default', 'vacuum']);

        $defaultMutexMock = $this->createMutexMock();
        $defaultMutexMock->expects($this->exactly(2))
            ->method('isLocked')
            ->willReturnOnConsecutiveCalls(true, false);

        $mutexFactoryMock = $this->createMock(MutexFactory::class);
        $mutexFactoryMock->expects($this->once())
            ->method('getPrefixedCronMutex')
            ->with('default')
            ->willReturn($defaultMutexMock);

        $cronControlFacade = new CronControlFacade(
            $this->createStub(LockInterface::class),
            $cronFacadeStub,
            $mutexFactoryMock,
        );

        $cronControlFacade->waitUntilCronInstancesAreFinished(['vacuum']);
    }

    private function createMutexMock(): Mutex
    {
        /** @var \NinjaMutex\Mutex|\PHPUnit\Framework\MockObject\MockObject $mutexMock */
        $mutexMock = $this->getMockBuilder(Mutex::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isLocked'])
            ->getMock();

        return $mutexMock;
    }
}
