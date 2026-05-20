<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginListener
{
    public const string ADMINISTRATION_FIREWALL = 'administration';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorActivityFacade $administratorActivityFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function onSecurityInteractiveLogin(LoginSuccessEvent $event): void
    {
        if ($event->getFirewallName() !== static::ADMINISTRATION_FIREWALL) {
            return;
        }

        $token = $event->getAuthenticatedToken();
        $user = $token->getUser();

        if ($user instanceof TimelimitLoginInterface) {
            $user->setLastActivity($this->clock->now());
        }

        if ($user instanceof UniqueLoginInterface) {
            $user->setLoginToken(uniqid('', true));
        }

        if ($user instanceof Administrator) {
            $this->administratorActivityFacade->create(
                $user,
                $event->getRequest()->getClientIp(),
            );
        }

        $this->em->flush();
    }
}
