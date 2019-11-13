<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdministratorRolesChangedSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    protected $rolesChanged;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade
     */
    protected $administratorFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(TokenStorageInterface $tokenStorage, AdministratorFacade $administratorFacade)
    {
        $this->tokenStorage = $tokenStorage;
        $this->administratorFacade = $administratorFacade;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        /* @var $administrator \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null */
        $administrator = null;
        if ($token !== null) {
            $administrator = $token->getUser();
        }

        if ($administrator instanceof Administrator && $this->rolesChanged === true) {
            $token = new UsernamePasswordToken($administrator, $administrator->getPassword(), 'administration', $administrator->getRoles());
            $this->tokenStorage->setToken($token);
            $this->administratorFacade->setRolesChangedNow($administrator);
            $this->rolesChanged = false;
        }
    }

    public function updateRoles(): void
    {
        $this->rolesChanged = true;
    }
}
