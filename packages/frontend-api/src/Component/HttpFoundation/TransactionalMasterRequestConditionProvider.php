<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use Override;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class TransactionalMasterRequestConditionProvider implements TransactionalMasterRequestConditionProviderInterface
{
    protected const QUERY_TYPE = 'query';

    public function __construct(
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly GraphqlOperationTypeResolver $graphqlOperationTypeResolver,
    ) {
    }

    #[Override]
    public function shouldBeginTransaction(RequestEvent $event): bool
    {
        return !$this->isRequestGraphQlQuery($event);
    }

    protected function isRequestGraphQlQuery(RequestEvent $requestEvent): bool
    {
        if (!$this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return false;
        }

        $requestContent = $requestEvent->getRequest()->getContent();

        if ($requestContent === null || $requestContent === '') {
            return false;
        }

        $source = json_decode($requestContent, true);

        if (!is_array($source)) {
            // The body can still carry a mutation (application/graphql or form content type), so keep the transaction
            return false;
        }

        $operationType = $this->graphqlOperationTypeResolver->resolveOperationTypeFromPayload($source);

        return $operationType === static::QUERY_TYPE;
    }
}
