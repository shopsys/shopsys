<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Messenger;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Redis;
use Shopsys\FrameworkBundle\Component\Messenger\CloseIdleConnectionSubscriber;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Worker;

class CloseIdleConnectionSubscriberTest extends TestCase
{
    private ManagerRegistry|MockObject $managerRegistry;

    private RedisFacade|MockObject $redisFacade;

    private CloseIdleConnectionSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->redisFacade = $this->createMock(RedisFacade::class);
        $this->subscriber = new CloseIdleConnectionSubscriber($this->managerRegistry, $this->redisFacade);
    }

    public function testGetSubscribedEvents(): void
    {
        $expected = [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];

        $this->assertEquals($expected, iterator_to_array(CloseIdleConnectionSubscriber::getSubscribedEvents()));
    }

    public function testWhenWorkerIsIdle(): void
    {
        $dbConnection1 = $this->createMock(Connection::class);
        $dbConnection2 = $this->createMock(Connection::class);

        $redisConnection = $this->createMock(Redis::class);

        $this->managerRegistry
            ->expects($this->once())
            ->method('getConnections')
            ->willReturn([$dbConnection1, $dbConnection2]);

        $this->redisFacade
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

        $worker = $this->createMock(Worker::class);
        $event = new WorkerRunningEvent($worker, true);

        $this->subscriber->onWorkerRunning($event);
    }

    public function testWhenWorkerIsNotIdle(): void
    {
        $this->managerRegistry
            ->expects($this->never())
            ->method('getConnections');

        $this->redisFacade
            ->expects($this->never())
            ->method('getConnections');

        $worker = $this->createMock(Worker::class);
        $event = new WorkerRunningEvent($worker, false);

        $this->subscriber->onWorkerRunning($event);
    }
}
