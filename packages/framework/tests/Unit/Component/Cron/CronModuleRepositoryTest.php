<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFactory;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Symfony\Component\Clock\Clock;

class CronModuleRepositoryTest extends TestCase
{
    public function testGetCronModuleReturnsCorrectInstance(): void
    {
        $doctrineRepositoryMock = $this->createNullDoctrineRepositoryMock();
        $em = $this->createEntityManagerMockWithRepository($doctrineRepositoryMock);

        $repository = new CronModuleRepository($em, new CronModuleFactory(new EntityNameResolver([])), Clock::get());
        $expectedServiceId = 'serviceId';
        $cronModule = $repository->getCronModuleByServiceId($expectedServiceId);
        $this->assertSame($expectedServiceId, $cronModule->getServiceId());
    }

    private function createEntityManagerMockWithRepository(
        EntityRepository $entityRepository,
    ): MockObject|EntityManagerInterface {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($entityRepository);

        return $em;
    }

    private function createNullDoctrineRepositoryMock(): MockObject|EntityRepository
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn(null);

        return $repository;
    }
}
