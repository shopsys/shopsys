<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Symfony\Component\HttpFoundation\RequestStack;

final class FrontendApiContext extends AbstractContext
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ResolveContextHelper $resolveContextHelper,
    ) {
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Matches requests to Frontend API endpoints (GraphQL)';
    }

    #[Override]
    public function matches(): bool
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            return false;
        }

        return $this->resolveContextHelper->requestPathMatchesPattern('graphql', $request->getPathInfo());
    }
}
