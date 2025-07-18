<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use Override;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdminContext extends AbstractContext
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
        return 'Matches requests to the administration UI';
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

        $route = $request->attributes->get('_route', '');

        return is_string($route) && str_starts_with($route, 'admin_');
    }
}
