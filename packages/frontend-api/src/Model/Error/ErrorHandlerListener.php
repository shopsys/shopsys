<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ErrorHandlerListener
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        $routeParam = $event->getRequest()->attributes->get('_route');

        if ($exception instanceof BadRequestHttpException && $this->isGraphQlRoute($routeParam)) {
            $errors = [
                'errors' => [
                    ['message' => $exception->getMessage()],
                ],
            ];

            $event->setResponse(new JsonResponse($errors));
        }
    }

    /**
     * @param string $routeParam
     * @return bool
     */
    protected function isGraphQlRoute(string $routeParam): bool
    {
        return in_array($routeParam, ['overblog_graphql_endpoint', 'overblog_graphql_batch_endpoint'], true);
    }
}
