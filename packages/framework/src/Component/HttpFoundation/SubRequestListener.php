<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\TooManyRedirectResponsesException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class SubRequestListener
{
    protected ?RedirectResponse $redirectResponse = null;

    protected ?Request $masterRequest = null;

    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->masterRequest = $event->getRequest();
        } elseif ($this->masterRequest !== null) {
            $this->fillSubRequestFromMasterRequest($event->getRequest());
        }
    }

    protected function fillSubRequestFromMasterRequest(Request $subRequest): void
    {
        $subRequest->setMethod($this->masterRequest->getMethod());
        $subRequest->request = $this->masterRequest->request;
        $subRequest->server = $this->masterRequest->server;
        $subRequest->files = $this->masterRequest->files;

        $subRequestQueryParameters = array_replace($this->masterRequest->query->all(), $subRequest->query->all());
        $subRequest->query->replace($subRequestQueryParameters);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest()) {
            if ($this->redirectResponse !== null) {
                $this->redirectResponse->send();
            }
        } else {
            $this->processSubResponse($event->getResponse());
        }
    }

    protected function processSubResponse(Response $subResponse): void
    {
        if ($subResponse instanceof RedirectResponse) {
            if ($this->redirectResponse !== null) {
                $message = 'Only one subresponse can do a redirect.';

                throw new TooManyRedirectResponsesException($message);
            }

            $this->redirectResponse = $subResponse;
        }
    }
}
