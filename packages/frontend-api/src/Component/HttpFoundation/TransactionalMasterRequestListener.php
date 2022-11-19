<?php

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Event\ExecutorResultEvent;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener as BaseTransactionalMasterRequestListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class TransactionalMasterRequestListener extends BaseTransactionalMasterRequestListener
{
    /**
     * @var bool
     */
    protected bool $hasGraphqlResponseErrors;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
        $this->hasGraphqlResponseErrors = false;
    }

    /**
     * @param \Overblog\GraphQLBundle\Event\ExecutorResultEvent $event
     */
    public function afterGraphQlExecution(ExecutorResultEvent $event): void
    {
        if (count($event->getResult()->errors) > 0) {
            $this->hasGraphqlResponseErrors = true;
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMasterRequest() && $this->hasGraphqlResponseErrors === true && $this->inTransaction === true){
            $this->em->rollback();
            $this->inTransaction = false;
        }

        parent::onKernelResponse($event);
    }


}