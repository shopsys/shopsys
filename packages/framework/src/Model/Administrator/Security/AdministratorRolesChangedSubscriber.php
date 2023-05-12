<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdministratorRolesChangedSubscriber implements EventSubscriberInterface
{
    protected bool $rolesChanged = false;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedFacade $administratorRolesChangedFacade
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly AdministratorRolesChangedFacade $administratorRolesChangedFacade
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $token = $this->tokenStorage->getToken();

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null $administrator */
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
