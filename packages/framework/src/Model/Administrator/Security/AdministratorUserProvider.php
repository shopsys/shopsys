<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security;

use DateTime;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AdministratorUserProvider implements UserProviderInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository
     */
    private $administratorRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade
     */
    private $administratorActivityFacade;

    public function __construct(
        AdministratorRepository $administratorRepository,
        AdministratorActivityFacade $administratorActivityFacade
    ) {
        $this->administratorRepository = $administratorRepository;
        $this->administratorActivityFacade = $administratorActivityFacade;
    }

    /**
     * @param string $username The username
     */
    public function loadUserByUsername($username): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $administrator = $this->administratorRepository->findByUserName($username);

        if ($administrator === null) {
            $message = sprintf(
                'Unable to find an active admin Shopsys\FrameworkBundle\Model\Administrator\Administrator object identified by "%s".',
                $username
            );
            throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException($message, 0);
        }

        return $administrator;
    }

    public function refreshUser(UserInterface $administrator): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $class = get_class($administrator);
        if (!$this->supportsClass($class)) {
            $message = sprintf('Instances of "%s" are not supported.', $class);
            throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException($message);
        }

        if ($administrator instanceof UniqueLoginInterface) {
            $freshAdministrator = $this->administratorRepository->findByIdAndLoginToken(
                $administrator->getId(),
                $administrator->getLoginToken()
            );
        } else {
            $freshAdministrator = $this->administratorRepository->findById($administrator->getId());
        }

        if ($administrator instanceof TimelimitLoginInterface) {
            if (time() - $administrator->getLastActivity()->getTimestamp() > 3600 * 5) {
                throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('Admin was too long unactive.');
            }
            if ($freshAdministrator !== null) {
                $freshAdministrator->setLastActivity(new DateTime());
            }
        }

        if ($freshAdministrator === null) {
            throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('Unable to find an active admin');
        }

        if ($freshAdministrator instanceof Administrator) {
            $this->administratorActivityFacade->updateCurrentActivityLastActionTime($freshAdministrator);
        }

        return $freshAdministrator;
    }

    /**
     * @param string $class
     */
    public function supportsClass($class): bool
    {
        return Administrator::class === $class || is_subclass_of($class, Administrator::class);
    }
}
