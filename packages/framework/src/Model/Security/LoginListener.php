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
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorActivityFacade $administratorActivityFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\LoginSuccessEvent $event
     */
    public function onSecurityInteractiveLogin(LoginSuccessEvent $event): void
    {
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
