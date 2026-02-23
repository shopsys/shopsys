<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\InMemoryUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorChecker extends InMemoryUserChecker
{
    public function __construct(
        protected readonly string $environment,
        protected readonly bool $ignoreDefaultAdminPasswordCheck,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $user
     */
    #[Override]
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if ($this->environment === EnvironmentType::PRODUCTION
            && !$this->ignoreDefaultAdminPasswordCheck
            && in_array($user->getUserIdentifier(), ['admin', 'superadmin'], true)
            && password_verify('admin123', $user->getPassword())
        ) {
            throw new LoginWithDefaultPasswordException();
        }

        // @phpstan-ignore arguments.count (Forward compatibility with Symfony 8 parent method signature)
        parent::checkPostAuth($user, $token);
    }
}
