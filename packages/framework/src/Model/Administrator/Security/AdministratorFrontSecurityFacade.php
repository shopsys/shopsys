<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorFrontSecurityFacade
{
    // same as in security.yaml
    public const ADMINISTRATION_CONTEXT = 'administration';

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly AdministratorUserProvider $administratorUserProvider,
        protected readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    public function isAdministratorLogged(): bool
    {
        try {
            $token = $this->getAdministratorToken();
        } catch (InvalidTokenException|AuthenticationException) {
            return false;
        }

        if ($token->getUser() === null) {
            return false;
        }

        return $this->accessDecisionManager->decide($token, [SystemRole::ADMIN]);
    }

    public function getCurrentAdministrator(): Administrator
    {
        if ($this->isAdministratorLogged()) {
            /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $user */
            $user = $this->getAdministratorToken()->getUser();

            return $user;
        }
        $message = 'Administrator is not logged.';

        throw new AdministratorIsNotLoggedException($message);
    }

    /**
     * @see \Symfony\Component\Security\Http\Firewall\ContextListener::handle()
     */
    protected function getAdministratorToken(): TokenInterface
    {
        try {
            $serializedToken = $this->requestStack->getSession()->get('_security_' . static::ADMINISTRATION_CONTEXT);
        } catch (SessionNotFoundException) {
            $serializedToken = null;
        }

        if ($serializedToken === null) {
            $message = 'Token not found.';

            throw new InvalidTokenException($message);
        }

        $token = unserialize($serializedToken);

        if (!$token instanceof TokenInterface) {
            $message = 'Token has invalid interface.';

            throw new InvalidTokenException($message);
        }
        $this->refreshUserInToken($token);

        return $token;
    }

    /**
     * @see \Symfony\Component\Security\Http\Firewall\ContextListener::handle()
     * @see \Symfony\Component\Security\Core\Authentication\Token\AbstractToken::setUser()
     */
    protected function refreshUserInToken(TokenInterface $token): void
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            $message = 'User in token must implement UserInterface.';

            throw new InvalidTokenException($message);
        }

        try {
            $freshUser = $this->administratorUserProvider->refreshUser($user);
        } catch (UnsupportedUserException $e) {
            $message = 'AdministratorUserProvider does not support user in this token.';

            throw new InvalidTokenException($message, $e);
        } catch (UserNotFoundException $e) {
            $message = 'Username not found.';

            throw new InvalidTokenException($message, $e);
        }

        $token->setUser($freshUser);
    }
}
