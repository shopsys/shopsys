<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class FlashBagProvider
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface|null
     */
    public function getFlashBag(): ?FlashBagInterface
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return null;
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $currentRequest->getSession();

        return $session->getFlashBag();
    }
}
