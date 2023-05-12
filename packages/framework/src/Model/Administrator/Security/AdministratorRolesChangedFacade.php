<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdministratorRolesChangedFacade
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(protected readonly TokenStorageInterface $tokenStorage, protected readonly AdministratorFacade $administratorFacade)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function refreshAdministratorToken(Administrator $administrator): void
    {
        $token = new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles());
        $this->tokenStorage->setToken($token);
        $this->administratorFacade->setRolesChangedNow($administrator);
    }
}
