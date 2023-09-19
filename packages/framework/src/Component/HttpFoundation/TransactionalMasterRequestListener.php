<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class TransactionalMasterRequestListener
{
    protected bool $inTransaction = false;

    protected static bool $isManuallyRollbacked = false;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public static function setTransactionManuallyRollbacked(): void
    {
        static::$isManuallyRollbacked = true;
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
        if ($event->isMainRequest() && $this->inTransaction && !static::$isManuallyRollbacked) {
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
