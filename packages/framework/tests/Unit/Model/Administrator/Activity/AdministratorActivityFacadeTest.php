<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Administrator\Activity;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFactory;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityRepository;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFacadeTest extends TestCase
{
    public function testUpdateCurrentActivityUpdatesAdministratorActivityAndFlushes(): void
    {
        $mockedNow = new DateTimeImmutable('2026-05-13 10:00:00');
        $administratorMock = $this->createMock(Administrator::class);
        $administratorMock->expects($this->once())->method('setLastActivity')->with($mockedNow);

        $administratorActivityMock = $this->createMock(AdministratorActivity::class);
        $administratorActivityMock->expects($this->once())->method('updateLastActionTime');

        $administratorActivityRepositoryMock = $this->createMock(AdministratorActivityRepository::class);
        $administratorActivityRepositoryMock
            ->expects($this->once())
            ->method('getCurrent')
            ->with($administratorMock)
            ->willReturn($administratorActivityMock);

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects($this->once())->method('flush');

        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn($mockedNow);

        $administratorActivityFacade = new AdministratorActivityFacade(
            $emMock,
            $administratorActivityRepositoryMock,
            $this->createStub(AdministratorActivityFactory::class),
            $clockStub,
        );

        $administratorActivityFacade->updateCurrentActivity($administratorMock);
    }
}
