<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use DateTime;
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
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository
     */
    protected $administratorRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade
     */
    protected $administratorActivityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedSubscriber
     */
    protected $administratorRolesChangedSubscriber;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository $administratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade $administratorActivityFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorRolesChangedSubscriber $administratorRolesChangedSubscriber
     */
    public function __construct(
        AdministratorRepository $administratorRepository,
        AdministratorActivityFacade $administratorActivityFacade,
        AdministratorRolesChangedSubscriber $administratorRolesChangedSubscriber
    ) {
        $this->administratorRepository = $administratorRepository;
        $this->administratorActivityFacade = $administratorActivityFacade;
        $this->administratorRolesChangedSubscriber = $administratorRolesChangedSubscriber;
    }

    /**
     * @param string $username The username
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function loadUserByUsername($username)
    {
        $administrator = $this->administratorRepository->findByUserName($username);

        if ($administrator === null) {
            $message = sprintf(
                'Unable to find an active admin Shopsys\FrameworkBundle\Model\Administrator\Administrator object identified by "%s".',
                $username
            );

            throw new UserNotFoundException($message, 0);
        }

        return $administrator;
    }

    /**
     * @param string $identifier
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function loadUserByIdentifier(string $identifier): Administrator
    {
        return $this->loadUserByUsername($identifier);
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $userInterface
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function refreshUser(UserInterface $userInterface)
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
            if (time() - $administrator->getLastActivity()->getTimestamp() > 3600 * 5) {
                throw new AuthenticationExpiredException('Admin was too long inactive.');
            }

            if ($freshAdministrator !== null) {
                $freshAdministrator->setLastActivity(new DateTime());
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

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === Administrator::class || is_subclass_of($class, Administrator::class);
    }
}
