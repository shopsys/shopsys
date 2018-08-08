<?php

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class TransactionalMasterRequestService
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var bool
     */
    private $inTransaction;

    public function __construct(EntityManagerInterface $em)
    {
        $this->inTransaction = false;
        $this->em = $em;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if ($event->isMasterRequest() && !$this->inTransaction) {
            $this->em->beginTransaction();
            $this->inTransaction = true;
        }
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if ($event->isMasterRequest() && $this->inTransaction) {
            $this->em->commit();
            $this->inTransaction = false;
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if ($event->isMasterRequest() && $this->inTransaction) {
            $this->em->rollback();
            $this->inTransaction = false;
        }
    }
}
