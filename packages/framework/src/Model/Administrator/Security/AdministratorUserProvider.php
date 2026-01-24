<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdministratorUserProvider implements UserProviderInterface
{
    public function __construct(
        protected readonly AdministratorRepository $administratorRepository,
        protected readonly AdministratorActivityFacade $administratorActivityFacade,
        protected readonly AdministratorRolesChangedSubscriber $administratorRolesChangedSubscriber,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $username The username
     */
    public function loadUserByUsername(string $username): Administrator
    {
        $administrator = $this->administratorRepository->findByUserNameWithPasswordFilled($username);

        if ($administrator === null) {
            $message = sprintf(
                'Unable to find an active admin Shopsys\FrameworkBundle\Model\Administrator\Administrator object identified by "%s".',
                $username,
            );

            throw new UserNotFoundException($message, 0);
        }

        return $administrator;
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): Administrator
    {
        return $this->loadUserByUsername($identifier);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[Override]
    public function refreshUser(UserInterface $userInterface): UserInterface
    {
        $class = get_class($userInterface);

        if (!$this->supportsClass($class)) {
            $message = sprintf('Instances of "%s" are not supported.', $class);

            throw new UnsupportedUserException($message);
        }

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $userInterface;

        $freshAdministrator = $this->administratorRepository->findById($administrator->getId());

        if ($administrator instanceof UniqueLoginInterface
            && $freshAdministrator !== null
            && $freshAdministrator->getLoginToken() !== $administrator->getLoginToken()
        ) {
            throw new AuthenticationExpiredException();
        }

        if ($administrator instanceof TimelimitLoginInterface) {
            if ($this->clock->now()->getTimestamp() - $administrator->getLastActivity()->getTimestamp() > 3600 * 5) {
                throw new AuthenticationExpiredException('Admin was too long inactive.');
            }

            if ($freshAdministrator !== null) {
                $freshAdministrator->setLastActivity($this->clock->now());
            }
        }

        if ($freshAdministrator === null) {
            throw new UserNotFoundException('Unable to find an active admin');
        }

        if ($freshAdministrator instanceof Administrator) {
            $this->administratorActivityFacade->updateCurrentActivityLastActionTime($freshAdministrator);
        }

        if ($freshAdministrator->getRolesChangedAt() > $administrator->getRolesChangedAt()) {
            //In this step token does not exist, so we are not able to update user roles.
            //We notify RolesChangedListener for roles updating
            $this->administratorRolesChangedSubscriber->updateRoles();
        }

        return $freshAdministrator;
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return $class === Administrator::class || is_subclass_of($class, Administrator::class);
    }
}
