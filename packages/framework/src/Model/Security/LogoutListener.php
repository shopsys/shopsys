<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener implements EventSubscriberInterface
{
    protected const ADMINISTRATION_TOKEN = 'administration';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\FrontLogoutHandler $frontLogoutHandler
     * @param \Shopsys\FrameworkBundle\Model\Security\AdminLogoutHandler $adminLogoutHandler
     */
    public function __construct(
        protected readonly FrontLogoutHandler $frontLogoutHandler,
        protected readonly AdminLogoutHandler $adminLogoutHandler,
    ) {
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\LogoutEvent $event
     */
    public function onLogout(LogoutEvent $event): void
    {
        if (!($event->getToken() instanceof UsernamePasswordToken)) {
            return;
        }

        /** @var \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken $token */
        $token = $event->getToken();

        if ($token->getFirewallName() === static::ADMINISTRATION_TOKEN) {
            $response = $this->adminLogoutHandler->onLogoutSuccess($event->getRequest());
        } else {
            $response = $this->frontLogoutHandler->onLogoutSuccess($event->getRequest());
        }

        $event->setResponse($response);
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout', 64],
        ];
    }
}
