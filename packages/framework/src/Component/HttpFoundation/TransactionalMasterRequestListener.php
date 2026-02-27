<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Traversable;

class TransactionalMasterRequestListener
{
    protected bool $inTransaction = false;

    /**
     * @param \Traversable<int, \Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface> $transactionalMasterRequestConditionProviders
     */
    public function __construct(
        protected readonly Traversable $transactionalMasterRequestConditionProviders,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->inTransaction && $this->shouldBeginTransaction($event)) {
            $this->em->beginTransaction();
            $this->inTransaction = true;
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->inTransaction) {
            return;
        }

        $this->em->commit();
        $this->inTransaction = false;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->rollbackTransaction();
    }

    public function onSilencedException(SilencedExceptionEvent $event): void
    {
        $this->rollbackTransaction();
    }

    protected function rollbackTransaction(): void
    {
        if (!$this->inTransaction) {
            return;
        }

        $this->em->rollback();
        $this->inTransaction = false;
    }

    protected function shouldBeginTransaction(RequestEvent $event): bool
    {
        foreach ($this->transactionalMasterRequestConditionProviders as $transactionalMasterRequestConditionProvider) {
            if (!$transactionalMasterRequestConditionProvider->shouldBeginTransaction($event)) {
                return false;
            }
        }

        return true;
    }
}
