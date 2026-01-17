<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdministratorRolesChangedFacade
{
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly AdministratorFacade $administratorFacade,
    ) {
    }

    public function refreshAdministratorToken(Administrator $administrator): void
    {
        $token = new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles());
        $this->tokenStorage->setToken($token);
        $this->administratorFacade->setRolesChangedNow($administrator);
    }
}
