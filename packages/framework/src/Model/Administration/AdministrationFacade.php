<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administration;

use Symfony\Component\HttpFoundation\RequestStack;

class AdministrationFacade
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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
