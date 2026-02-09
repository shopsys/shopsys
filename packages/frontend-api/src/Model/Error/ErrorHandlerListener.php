<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ErrorHandlerListener
{
    public function __construct(
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (!($throwable instanceof BadRequestHttpException) || !$this->contextResolver->isCurrentContext(FrontendApiContext::class)) {
            return;
        }

        $errors = [
            'errors' => [
                ['message' => $throwable->getMessage()],
            ],
        ];

        $event->setResponse(new JsonResponse($errors));
    }
}
