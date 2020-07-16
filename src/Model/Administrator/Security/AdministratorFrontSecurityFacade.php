<?php

declare(strict_types=1);

namespace App\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade as BaseAdministratorFrontSecurityFacade;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 */
class AdministratorFrontSecurityFacade extends BaseAdministratorFrontSecurityFacade
{
    /**
     * @return bool
     */
    public function isAdministratorLogged(): bool
    {
        try {
            return parent::isAdministratorLogged();
        } catch (AuthenticationExpiredException | UnsupportedUserException | UsernameNotFoundException $exception) {
            return false;
        }
    }
}
