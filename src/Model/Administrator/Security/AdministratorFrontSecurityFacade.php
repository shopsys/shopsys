<?php

declare(strict_types=1);

namespace App\Model\Administrator\Security;

use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade as BaseAdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
            $token = $this->getAdministratorToken();
        } catch (
            InvalidTokenException |
            AuthenticationException |
            AuthenticationExpiredException |
            UnsupportedUserException |
            UsernameNotFoundException $e
        ) {
            return false;
        }

        if (!$token->isAuthenticated()) {
            return false;
        }

        return $this->accessDecisionManager->decide($token, [Roles::ROLE_ADMIN, Roles::ROLE_CUSTOMER_CARE]);
    }
}
