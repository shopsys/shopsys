<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;
use Override;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestConditionProviderInterface;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class TransactionalMasterRequestConditionProvider implements TransactionalMasterRequestConditionProviderInterface
{
    protected const QUERY_TYPE = 'query';

    public function __construct(
        protected readonly ContextResolverInterface $contextResolver,
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
            throw new InvalidArgumentUserError('Request content is not a valid JSON.');
        }

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
