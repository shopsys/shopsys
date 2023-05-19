<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorFrontSecurityFacade
{
    // same as in security.yaml
    public const ADMINISTRATION_CONTEXT = 'administration';

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorUserProvider $administratorUserProvider
     * @param \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $accessDecisionManager
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly AdministratorUserProvider $administratorUserProvider,
        protected readonly AccessDecisionManagerInterface $accessDecisionManager,
        protected readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @return bool
     */
    public function isAdministratorLogged(): bool
    {
        try {
            $token = $this->getAdministratorToken();
        } catch (InvalidTokenException | AuthenticationException) {
            return false;
        }

        if ($token->getUser() === null) {
            return false;
        }

        return $this->accessDecisionManager->decide($token, [Roles::ROLE_ADMIN]);
    }

    /**
     * @return bool
     */
    public function isAdministratorLoggedAsCustomer(): bool
    {
        try {
            return $this->requestStack->getSession()->has(LoginAsUserFacade::SESSION_LOGIN_AS);
        } catch (SessionNotFoundException) {
            return false;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
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
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
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
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
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
