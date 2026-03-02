<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorResultEvent;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GraphqlMutationErrorRollbackSubscriber implements EventSubscriberInterface
{
    protected const string MUTATION_OPERATION = 'mutation';

    protected bool $shouldRollbackTransaction = false;

    public function __construct(
        protected readonly GraphqlOperationTypeResolver $graphqlOperationTypeResolver,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @return array<string, string|array<int, array{0: string, 1?: int}>>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_EXECUTOR => [
                ['onPostExecutor'],
            ],
            KernelEvents::RESPONSE => [
                ['onKernelResponse', 10],
            ],
        ];
    }

    public function onPostExecutor(ExecutorResultEvent $event): void
    {
        if (count($event->getResult()->errors) === 0) {
            return;
        }

        $operationType = $this->graphqlOperationTypeResolver->resolveOperationTypeFromPayload([
            'query' => $event->getExecutorArguments()->getRequestString(),
            'operationName' => $event->getExecutorArguments()->getOperationName(),
        ]);

        if ($operationType !== static::MUTATION_OPERATION) {
            return;
        }

        $this->shouldRollbackTransaction = true;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->shouldRollbackTransaction) {
            return;
        }

        $this->shouldRollbackTransaction = false;
        $this->eventDispatcher->dispatch(new SilencedExceptionEvent());
    }
}
