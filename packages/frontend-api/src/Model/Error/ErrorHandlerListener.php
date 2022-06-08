<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ErrorHandlerListener
{
    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $routeParam = $event->getRequest()->attributes->get('_route');

        if (!($throwable instanceof BadRequestHttpException) || !$this->isGraphQlRoute($routeParam)) {
            return;
        }

        $errors = [
            'errors' => [
                ['message' => $throwable->getMessage()],
            ],
        ];

        $event->setResponse(new JsonResponse($errors));
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
