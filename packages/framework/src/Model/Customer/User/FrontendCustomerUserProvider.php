<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendCustomerUserProvider implements UserProviderInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(CustomerUserRepository $customerUserRepository, Domain $domain)
    {
        $this->customerUserRepository = $customerUserRepository;
        $this->domain = $domain;
    }

    /**
     * @param string $email
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function loadUserByUsername($email)
    {
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(mb_strtolower($email), $this->domain->getId());

        if ($customerUser === null) {
            $message = sprintf(
                'Unable to find an active Shopsys\FrameworkBundle\Model\Customer\User object identified by email "%s".',
                $email
            );
            throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException($message, 0);
        }

        return $customerUser;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $userInterface
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function refreshUser(UserInterface $userInterface)
    {
        $class = get_class($userInterface);
        if (!$this->supportsClass($class)) {
            $message = sprintf('Instances of "%s" are not supported.', $class);
            throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException($message);
        }

        /** @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $userInterface;

        if ($customerUser instanceof TimelimitLoginInterface) {
            if (time() - $customerUser->getLastActivity()->getTimestamp() > 3600 * 24) {
                throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('User was too long unactive');
            }
            $customerUser->setLastActivity(new DateTime());
        }

        if ($customerUser instanceof UniqueLoginInterface) {
            $freshCustomerUser = $this->customerUserRepository->findByIdAndLoginToken($customerUser->getId(), $customerUser->getLoginToken());
        } else {
            $freshCustomerUser = $this->customerUserRepository->findById($customerUser->getId());
        }

        if ($freshCustomerUser === null) {
            throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('Unable to find an active user');
        }

        return $freshCustomerUser;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === CustomerUser::class || is_subclass_of($class, CustomerUser::class);
    }
}
