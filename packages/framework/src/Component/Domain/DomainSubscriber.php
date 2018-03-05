<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DomainSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            try {
                $this->domain->getId();
            } catch (\Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException $exception) {
                $this->domain->switchDomainByRequest($event->getRequest());
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            // Setting domain by request must be done before loading other services (eg.: routing, localization...)
            KernelEvents::REQUEST => [['onKernelRequest', 100]],
        ];
    }
}
