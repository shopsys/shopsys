<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\HttpFoundation;

use ArrayIterator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Traversable;

class TransactionalMasterRequestListenerTest extends TestCase
{
    public function testBeginsAndCommitsTransactionOnSuccessfulRequest(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())
            ->method('beginTransaction');
        $entityManagerMock->expects($this->once())
            ->method('commit');
        $entityManagerMock->expects($this->never())
            ->method('rollback');

        $listener = new TransactionalMasterRequestListener(
            $this->createTransactionalMasterRequestConditionProviders(),
            $entityManagerMock,
        );

        $listener->onKernelRequest($this->createMainRequestEvent());
        $listener->onKernelResponse($this->createMainResponseEvent());
    }

    public function testSilencedExceptionRollbackDoesNotLeakToFollowingRequest(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->exactly(2))
            ->method('beginTransaction');
        $entityManagerMock->expects($this->once())
            ->method('commit');
        $entityManagerMock->expects($this->once())
            ->method('rollback');

        $listener = new TransactionalMasterRequestListener(
            $this->createTransactionalMasterRequestConditionProviders(),
            $entityManagerMock,
        );

        $listener->onKernelRequest($this->createMainRequestEvent());
        $listener->onSilencedException(new SilencedExceptionEvent());

        $listener->onKernelRequest($this->createMainRequestEvent());
        $listener->onKernelResponse($this->createMainResponseEvent());
    }

    public function testExceptionRollbackDoesNotLeakToFollowingRequest(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->exactly(2))
            ->method('beginTransaction');
        $entityManagerMock->expects($this->once())
            ->method('commit');
        $entityManagerMock->expects($this->once())
            ->method('rollback');

        $listener = new TransactionalMasterRequestListener(
            $this->createTransactionalMasterRequestConditionProviders(),
            $entityManagerMock,
        );

        $listener->onKernelRequest($this->createMainRequestEvent());
        $listener->onKernelException($this->createMainExceptionEvent());

        $listener->onKernelRequest($this->createMainRequestEvent());
        $listener->onKernelResponse($this->createMainResponseEvent());
    }

    /**
     * @return \Traversable<int, \Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface>
     */
    private function createTransactionalMasterRequestConditionProviders(): Traversable
    {
        /** @var \PHPUnit\Framework\MockObject\Stub&\Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface $conditionProviderStub */
        $conditionProviderStub = $this->createStub(TransactionalMasterRequestConditionProviderInterface::class);
        $conditionProviderStub->method('shouldBeginTransaction')
            ->willReturn(true);

        return new ArrayIterator([$conditionProviderStub]);
    }

    private function createMainRequestEvent(): RequestEvent
    {
        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    private function createMainResponseEvent(): ResponseEvent
    {
        return new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );
    }

    private function createMainExceptionEvent(): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new RuntimeException(),
        );
    }
}
