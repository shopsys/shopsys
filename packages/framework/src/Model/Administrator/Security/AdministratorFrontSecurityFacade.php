<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorUserProvider
     */
    protected $administratorUserProvider;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface
     */
    protected $accessDecisionManager;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorUserProvider $administratorUserProvider
     * @param \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $accessDecisionManager
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        SessionInterface $session,
        AdministratorUserProvider $administratorUserProvider,
        AccessDecisionManagerInterface $accessDecisionManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->session = $session;
        $this->administratorUserProvider = $administratorUserProvider;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return bool
     */
    public function isAdministratorLogged()
    {
        try {
            $token = $this->getAdministratorToken();
        } catch (
            InvalidTokenException |
            AuthenticationException $e
        ) {
            return false;
        }

        if (!$token->isAuthenticated()) {
            return false;
        }

        return $this->accessDecisionManager->decide($token, [Roles::ROLE_ADMIN]);
    }

    /**
     * @return bool
     */
    public function isAdministratorLoggedAsCustomer()
    {
        return $this->session->has(LoginAsUserFacade::SESSION_LOGIN_AS);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getCurrentAdministrator()
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
    protected function getAdministratorToken()
    {
        $serializedToken = $this->session->get('_security_' . static::ADMINISTRATION_CONTEXT);
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
    protected function refreshUserInToken(TokenInterface $token)
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
