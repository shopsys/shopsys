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

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->masterRequest = $event->getRequest();
        } elseif ($this->masterRequest !== null) {
            $this->fillSubRequestFromMasterRequest($event->getRequest());
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $subRequest
     */
    protected function fillSubRequestFromMasterRequest(Request $subRequest)
    {
        $subRequest->setMethod($this->masterRequest->getMethod());
        $subRequest->request = $this->masterRequest->request;
        $subRequest->server = $this->masterRequest->server;
        $subRequest->files = $this->masterRequest->files;

        $subRequestQueryParameters = array_replace($this->masterRequest->query->all(), $subRequest->query->all());
        $subRequest->query->replace($subRequestQueryParameters);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
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

    /**
     * @param \Symfony\Component\HttpFoundation\Response $subResponse
     */
    protected function processSubResponse(Response $subResponse)
    {
        if ($subResponse->isRedirection()) {
            if ($this->redirectResponse !== null) {
                $message = 'Only one subresponse can do a redirect.';

                throw new TooManyRedirectResponsesException($message);
            }

            /** @var \Symfony\Component\HttpFoundation\RedirectResponse $subRedirectResponse */
            $subRedirectResponse = $subResponse;
            $this->redirectResponse = $subRedirectResponse;
        }
    }
}
