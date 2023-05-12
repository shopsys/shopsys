<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administration;

use Symfony\Component\HttpFoundation\RequestStack;

class AdministrationFacade
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(protected readonly RequestStack $requestStack)
    {
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

        return str_starts_with($mainRequest->attributes->get('_route', ''), 'admin_');
    }
}
