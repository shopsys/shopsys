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
        return preg_match('/^(admin|app_admin)_/', $this->requestStack->getMasterRequest()->attributes->get('_route')) === 1;
    }
}
