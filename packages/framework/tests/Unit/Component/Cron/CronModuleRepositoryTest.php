<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFactory;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Symfony\Component\Clock\Clock;

class CronModuleRepositoryTest extends TestCase
{
    public function testGetCronModuleReturnsCorrectInstance(): void
    {
        $doctrineRepositoryStub = $this->createNullDoctrineRepositoryStub();
        $em = $this->createEntityManagerStubWithRepository($doctrineRepositoryStub);

        $repository = new CronModuleRepository($em, new CronModuleFactory(new EntityNameResolver([])), Clock::get());
        $expectedServiceId = 'serviceId';
        $cronModule = $repository->getCronModuleByServiceId($expectedServiceId);
        $this->assertSame($expectedServiceId, $cronModule->getServiceId());
    }

    private function createEntityManagerStubWithRepository(
        EntityRepository $entityRepository,
    ): EntityManagerInterface {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($entityRepository);

        return $em;
    }

    private function createNullDoctrineRepositoryStub(): EntityRepository
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('find')->willReturn(null);

        return $repository;
    }
}
