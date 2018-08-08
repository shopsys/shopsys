<?php

namespace Shopsys\FrameworkBundle\Component\Log;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SlowLogSubscriber implements EventSubscriberInterface
{
    const REQUEST_TIME_LIMIT_SECONDS = 2;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var float
     */
    protected $startTime;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->startTime = 0;
    }

    public function initStartTime(GetResponseEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $this->startTime = microtime(true);
        }
    }

    public function addNotice(PostResponseEvent $event): void
    {
        $requestTime = $this->getRequestTime();
        if ($requestTime > self::REQUEST_TIME_LIMIT_SECONDS) {
            $requestUri = $event->getRequest()->getRequestUri();
            $controllerNameAndAction = $event->getRequest()->get('_controller');

            $message = $requestTime . ' ' . $controllerNameAndAction . ' ' . $requestUri;
            $this->logger->addNotice($message);
        }
    }

    protected function getRequestTime(): float
    {
        return microtime(true) - $this->startTime;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'initStartTime',
            KernelEvents::TERMINATE => 'addNotice',
        ];
    }
}
