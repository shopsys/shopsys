<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseListener
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set('X-Frame-Options', 'sameorigin');
        $event->getResponse()->headers->set('X-XSS-Protection', '1; mode=block');
        $event->getResponse()->headers->set('X-Content-Type-Options', 'nosniff');
        $event->getResponse()->headers->set('X-Powered-By', 'Shopsys Framework');
    }
}
