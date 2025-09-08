<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Symfony\Component\HttpFoundation\RequestStack;

final class FrontendApiContext extends AbstractContext
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Matches requests to Frontend API endpoints (GraphQL)';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function matches(): bool
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            return false;
        }

        return in_array($request->attributes->get('_route'), ['overblog_graphql_endpoint', 'overblog_graphql_batch_endpoint'], true);
    }
}
