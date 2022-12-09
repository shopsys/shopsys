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
        $masterRequest = $this->requestStack->getMainRequest();

        if ($masterRequest === null) {
            return false;
        }

        return preg_match('/^admin_/', $masterRequest->attributes->get('_route')) === 1;
    }
}
