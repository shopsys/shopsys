<?php

declare(strict_types=1);

namespace App\Model\Security;

use App\Model\Administrator\Administrator;
use App\Model\Customer\User\CustomerUser;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade;
use Shopsys\FrameworkBundle\Model\Security\LoginListener as BaseLoginListener;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener extends BaseLoginListener
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private SessionInterface $session;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade $orderFlowFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        EntityManagerInterface $em,
        OrderFlowFacade $orderFlowFacade,
        AdministratorActivityFacade $administratorActivityFacade,
        SessionInterface $session
    ) {
        parent::__construct($em, $orderFlowFacade, $administratorActivityFacade);

        $this->session = $session;
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
            // this resets only 3th step in order flow
            $orderFlowData = $this->session->get('craue_form_flow', []);
            if (isset($orderFlowData['order']['flow_order']['data'][3]) === true) {
                unset($orderFlowData['order']['flow_order']['data'][3]);
                $this->session->set('craue_form_flow', $orderFlowData);
            }
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
