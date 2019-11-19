<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade
     */
    protected $administratorRolesChangedFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AdministratorRolesChangedFacade $administratorRolesChangedFacade
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->administratorRolesChangedFacade = $administratorRolesChangedFacade;
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
            $this->administratorRolesChangedFacade->refreshAdministratorToken($administrator);
        }
    }

    public function updateRoles(): void
    {
        $this->rolesChanged = true;
    }
}
