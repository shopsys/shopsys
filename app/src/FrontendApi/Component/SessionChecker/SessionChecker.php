<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\SessionChecker;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class SessionChecker
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasSession() || !$request->getSession()->isStarted() || !str_contains($request->getRequestUri(), 'graphql')) {
            return;
        }
        $response = $event->getResponse();
        $response->setContent('Session must not be started in the FE API. Check your code, please');
    }
}
