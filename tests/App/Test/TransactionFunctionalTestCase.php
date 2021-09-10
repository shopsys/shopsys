<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class TransactionFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     * @inject
     */
    protected EntityManagerInterface $em;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     * @inject
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * Runs scheduled recalculations that would be executed on a kernel.response event
     * This allows to clean scheduled recalculations before making request on a client that could break the application
     * Eg. when testing GraphQL validation that breaks consistency of the entity and disallows any operation over it afterwards
     */
    protected function dispatchFakeKernelResponseEventToTriggerImmediateRecalculations(): void
    {
        $fakeKernelResponseEvent = new ResponseEvent(
            $this->getCurrentClient()->getKernel(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            new Response()
        );

        /* @phpstan-ignore-next-line */
        $this->eventDispatcher->dispatch($fakeKernelResponseEvent, 'kernel.response');
    }
}
