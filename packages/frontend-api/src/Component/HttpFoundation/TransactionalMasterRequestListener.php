<?php

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Event\ExecutorResultEvent;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener as BaseTransactionalMasterRequestListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class TransactionalMasterRequestListener extends BaseTransactionalMasterRequestListener
{
    protected const GRAPHQL_ENDPOINT_ROUTE = 'overblog_graphql_endpoint';
    protected const QUERY_TYPE = 'query';

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
        foreach ($event->getResult()->errors as $error) {
            if ($error->getCategory() === Error::CATEGORY_INTERNAL) {
                $this->hasGraphqlResponseErrors = true;
                break;
            }
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMasterRequest() && $this->hasGraphqlResponseErrors === true && $this->inTransaction === true) {
            $this->em->rollback();
            $this->inTransaction = false;
        }

        parent::onKernelResponse($event);
    }
}
