<?php

namespace Shopsys\FrameworkBundle\Component\Log;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SlowLogSubscriber implements EventSubscriberInterface
{
    protected const REQUEST_TIME_LIMIT_SECONDS = 2;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['initStartTime', 512],
            KernelEvents::TERMINATE => 'addNotice',
        ];
    }
}
