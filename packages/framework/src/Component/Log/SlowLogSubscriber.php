<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Log;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SlowLogSubscriber implements EventSubscriberInterface
{
    protected const REQUEST_TIME_LIMIT_SECONDS = 2;

    protected float $startTime;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
        $this->startTime = 0;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function initStartTime(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->startTime = microtime(true);
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
     */
    public function addNotice(TerminateEvent $event): void
    {
        $requestTime = $this->getRequestTime();

        if ($requestTime <= static::REQUEST_TIME_LIMIT_SECONDS) {
            return;
        }

        $requestUri = $event->getRequest()->getRequestUri();
        $controllerNameAndAction = $event->getRequest()->get('_controller');

        $message = $requestTime . ' ' . $controllerNameAndAction . ' ' . $requestUri;
        $this->logger->notice($message);
    }

    /**
     * @return float
     */
    protected function getRequestTime()
    {
        return microtime(true) - $this->startTime;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['initStartTime', 512],
            KernelEvents::TERMINATE => 'addNotice',
        ];
    }
}
