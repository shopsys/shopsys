<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade $orderFlowFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderFlowFacade $orderFlowFacade,
        protected readonly AdministratorActivityFacade $administratorActivityFacade
    ) {
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if ($user instanceof TimelimitLoginInterface) {
            $user->setLastActivity(new DateTime());
        }

        if ($user instanceof CustomerUser) {
            $user->onLogin();
            $this->orderFlowFacade->resetOrderForm();
        }

        if ($user instanceof UniqueLoginInterface && !$user->isMultidomainLogin()) {
            $user->setLoginToken(uniqid('', true));
        }

        if ($user instanceof Administrator) {
            $this->administratorActivityFacade->create(
                $user,
                $event->getRequest()->getClientIp()
            );
        }

        $this->em->flush();
    }
}
