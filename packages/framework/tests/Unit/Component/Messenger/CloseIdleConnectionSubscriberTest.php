<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Messenger;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Redis;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Messenger\CloseIdleConnectionSubscriber;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Worker;

class CloseIdleConnectionSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $expected = [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];

        $this->assertEquals($expected, iterator_to_array(ExtendedClassNameResolver::resolve(CloseIdleConnectionSubscriber::class)::getSubscribedEvents()));
    }

    public function testWhenWorkerIsIdle(): void
    {
        $dbConnection1 = $this->createMock(Connection::class);
        $dbConnection2 = $this->createMock(Connection::class);

        $redisConnection = $this->createMock(Redis::class);

        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock
            ->expects($this->once())
            ->method('getConnections')
            ->willReturn([$dbConnection1, $dbConnection2]);

        $redisFacadeMock = $this->createMock(RedisFacade::class);
        $redisFacadeMock
            ->expects($this->once())
            ->method('getConnections')
            ->willReturn([$redisConnection]);

        $redisConnection
            ->expects($this->once())
            ->method('close');

        $dbConnection1
            ->expects($this->once())
            ->method('close');

        $dbConnection2
            ->expects($this->once())
            ->method('close');

        $worker = $this->createStub(Worker::class);
        $event = new WorkerRunningEvent($worker, true);

        $this->createSubscriber($managerRegistryMock, $redisFacadeMock)->onWorkerRunning($event);
    }

    public function testWhenWorkerIsNotIdle(): void
    {
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock
            ->expects($this->never())
            ->method('getConnections');

        $redisFacadeMock = $this->createMock(RedisFacade::class);
        $redisFacadeMock
            ->expects($this->never())
            ->method('getConnections');

        $worker = $this->createStub(Worker::class);
        $event = new WorkerRunningEvent($worker, false);

        $this->createSubscriber($managerRegistryMock, $redisFacadeMock)->onWorkerRunning($event);
    }

    public function testOnlyInitializedLazyRedisProxiesAreClosed(): void
    {
        $neverUsedRedis = new LazyRedisProxyStub(initialized: false);
        $usedRedis = new LazyRedisProxyStub(initialized: true);

        $redisFacadeStub = $this->createStub(RedisFacade::class);
        $redisFacadeStub->method('getConnections')->willReturn([$neverUsedRedis, $usedRedis]);

        $this->createSubscriber($this->createStub(ManagerRegistry::class), $redisFacadeStub)->onWorkerRunning(new WorkerRunningEvent($this->createStub(Worker::class), true));

        $this->assertFalse($neverUsedRedis->closed, 'closing a never used lazy proxy would connect it first');
        $this->assertTrue($usedRedis->closed);
    }

    private function createSubscriber(
        ManagerRegistry $managerRegistry,
        RedisFacade $redisFacade,
    ): CloseIdleConnectionSubscriber {
        return new CloseIdleConnectionSubscriber($managerRegistry, $redisFacade);
    }
}
