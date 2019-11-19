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
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function refreshAdministratorToken(Administrator $administrator): void
    {
        $token = new UsernamePasswordToken($administrator, $administrator->getPassword(), 'administration', $administrator->getRoles());
        $this->tokenStorage->setToken($token);
        $this->administratorFacade->setRolesChangedNow($administrator);
    }
}
