<?php

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class TransactionalMasterRequestListener
{
    protected bool $inTransaction;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
        $this->inTransaction = false;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest() && !$this->inTransaction) {
            $this->em->beginTransaction();
            $this->inTransaction = true;
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest() && $this->inTransaction) {
            $this->em->commit();
            $this->inTransaction = false;
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->isMainRequest() && $this->inTransaction) {
            $this->em->rollback();
            $this->inTransaction = false;
        }
    }
}
