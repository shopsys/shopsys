<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\Model\Administrator\Administrator;
use App\Model\Customer\User\CustomerUser;
use DateTime;
use Shopsys\FrameworkBundle\Model\Security\LoginListener as BaseLoginListener;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener extends BaseLoginListener
{
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
            // this resets order flow after log in
            // $this->orderFlowFacade->resetOrderForm();
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
