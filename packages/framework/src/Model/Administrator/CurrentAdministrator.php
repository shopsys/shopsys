<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentAdministrator
{
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function getCurrentlyLoggedAdministrator(): Administrator
    {
        $administrator = $this->tokenStorage->getToken()?->getUser();

        if (!$administrator instanceof Administrator) {
            throw new AdministratorIsNotLoggedException('Administrator is not logged.');
        }

        return $administrator;
    }
}
