<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class TransactionalMasterRequestConditionProvider implements TransactionalMasterRequestConditionProviderInterface
{
    protected const GRAPHQL_ENDPOINT_ROUTE = 'overblog_graphql_endpoint';
    protected const QUERY_TYPE = 'query';

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     * @return bool
     */
    #[Override]
    public function shouldBeginTransaction(RequestEvent $event): bool
    {
        return !$this->isRequestGraphQlQuery($event);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $requestEvent
     * @return bool
     */
    protected function isRequestGraphQlQuery(RequestEvent $requestEvent): bool
    {
        if ($requestEvent->getRequest()->attributes->get('_route') !== static::GRAPHQL_ENDPOINT_ROUTE) {
            return false;
        }

        $requestContent = $requestEvent->getRequest()->getContent();

        if ($requestContent === null || $requestContent === '') {
            return false;
        }

        $source = json_decode($requestContent, true);

        if (!array_key_exists(static::QUERY_TYPE, $source)) {
            return false;
        }

        $queryString = $source[static::QUERY_TYPE];

        try {
            $parsed = Parser::parse($queryString);

            foreach ($parsed->definitions as $definition) {
                if ($definition instanceof OperationDefinitionNode) {
                    return $definition->operation === static::QUERY_TYPE;
                }
            }
        } catch (SyntaxError) {
            return false;
        }

        return false;
    }
}
