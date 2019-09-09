<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AdministratorChecker extends UserChecker
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var bool
     */
    protected $ignoreDefaultAdminPasswordCheck;

    /**
     * @param string $environment
     * @param bool $ignoreDefaultAdminPasswordCheck
     */
    public function __construct(string $environment, bool $ignoreDefaultAdminPasswordCheck)
    {
        $this->environment = $environment;
        $this->ignoreDefaultAdminPasswordCheck = $ignoreDefaultAdminPasswordCheck;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function checkPreAuth(UserInterface $user)
    {
        if ($this->environment === EnvironmentType::PRODUCTION
            && !$this->ignoreDefaultAdminPasswordCheck
            && in_array($user->getUsername(), ['admin', 'superadmin'], true)
            && password_verify('admin123', $user->getPassword())
        ) {
            throw new LoginWithDefaultPasswordException();
        }

        return parent::checkPreAuth($user);
    }
}
