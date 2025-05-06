<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administration;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class AdministrationFacade
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
    ) {
    }

    /**
     * @return bool
     */
    public function isInAdmin(): bool
    {
        $mainRequest = $this->requestStack->getMainRequest();

        if ($mainRequest === null) {
            return false;
        }

        return str_starts_with($this->router->match($mainRequest->getPathInfo())['_route'], 'admin_');
    }
}
