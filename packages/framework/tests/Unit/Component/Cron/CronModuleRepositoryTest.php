<?php

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\CronModule;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleFactory;
use Shopsys\FrameworkBundle\Component\Cron\CronModuleRepository;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CronModuleRepositoryTest extends TestCase
{
    public function testGetCronModuleReturnsCorrectInstance(): void
    {
        $doctrineRepositoryMock = $this->createNullDoctrineRepositoryMock();
        $em = $this->createEntityManagerMockWithRepository($doctrineRepositoryMock);

        $repository = new CronModuleRepository($em, new CronModuleFactory(new EntityNameResolver([])));
        $cronModule = $repository->getCronModuleByServiceId('serviceId');
        $this->assertInstanceOf(CronModule::class, $cronModule);
    }

    /**
     * @param \Doctrine\ORM\EntityRepository $entityRepository
     * @return \PHPUnit\Framework\MockObject\MockObject|\Doctrine\ORM\EntityManagerInterface
     */
    private function createEntityManagerMockWithRepository(EntityRepository $entityRepository): MockObject|EntityManagerInterface
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($entityRepository);
        return $em;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Doctrine\ORM\EntityRepository
     */
    private function createNullDoctrineRepositoryMock(): MockObject|EntityRepository
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn(null);
        return $repository;
    }
}
