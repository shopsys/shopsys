<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Symfony\Component\Security\Core\User\InMemoryUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorChecker extends InMemoryUserChecker
{
    /**
     * @param string $environment
     * @param bool $ignoreDefaultAdminPasswordCheck
     */
    public function __construct(
        protected readonly string $environment,
        protected readonly bool $ignoreDefaultAdminPasswordCheck,
    ) {
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function checkPostAuth(UserInterface $user)
    {
        if ($this->environment === EnvironmentType::PRODUCTION
            && !$this->ignoreDefaultAdminPasswordCheck
            && in_array($user->getUserIdentifier(), ['admin', 'superadmin'], true)
            && password_verify('admin123', $user->getPassword())
        ) {
            throw new LoginWithDefaultPasswordException();
        }

        parent::checkPostAuth($user);
    }
}
