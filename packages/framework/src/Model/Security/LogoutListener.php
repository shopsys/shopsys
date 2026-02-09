<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener implements EventSubscriberInterface
{
    protected const string ADMINISTRATION_TOKEN = 'administration';

    public function __construct(
        protected readonly AdminLogoutHandler $adminLogoutHandler,
    ) {
    }

    public function onLogout(LogoutEvent $event): void
    {
        if (!($event->getToken() instanceof UsernamePasswordToken)) {
            return;
        }

        /** @var \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken $token */
        $token = $event->getToken();

        if ($token->getFirewallName() !== static::ADMINISTRATION_TOKEN) {
            return;
        }

        $response = $this->adminLogoutHandler->onLogoutSuccess($event->getRequest());

        $event->setResponse($response);
    }

    /**
     * @return array[]
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout', 64],
        ];
    }
}
