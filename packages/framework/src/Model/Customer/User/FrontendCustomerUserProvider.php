<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Shopsys\FrameworkBundle\Model\Security\UniqueLoginInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendCustomerUserProvider implements UserProviderInterface
{
    public function __construct(
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly Domain $domain,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $email
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function loadUserByUsername($email)
    {
        $customerUser = $this->customerUserRepository->findCustomerUserByEmailAndDomain(
            mb_strtolower($email),
            $this->domain->getId(),
        );

        if ($customerUser === null) {
            $message = sprintf(
                'Unable to find an active CustomerUser entity identified by email "%s".',
                $email,
            );

            throw new UserNotFoundException($message, 0);
        }

        return $customerUser;
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): CustomerUser
    {
        return $this->loadUserByUsername($identifier);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    #[Override]
    public function refreshUser(UserInterface $userInterface): UserInterface
    {
        $class = get_class($userInterface);

        if (!$this->supportsClass($class)) {
            $message = sprintf('Instances of "%s" are not supported.', $class);

            throw new UnsupportedUserException($message);
        }

        /** @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $userInterface;

        if ($customerUser instanceof TimelimitLoginInterface) {
            if (time() - $customerUser->getLastActivity()->getTimestamp() > 3600 * 24) {
                throw new UserNotFoundException('User was too long unactive');
            }
            $customerUser->setLastActivity($this->clock->now());
        }

        if ($customerUser instanceof UniqueLoginInterface) {
            $freshCustomerUser = $this->customerUserRepository->findByIdAndLoginToken(
                $customerUser->getId(),
                $customerUser->getLoginToken(),
            );
        } else {
            $freshCustomerUser = $this->customerUserRepository->findById($customerUser->getId());
        }

        if ($freshCustomerUser === null) {
            throw new UserNotFoundException('Unable to find an active user');
        }

        return $freshCustomerUser;
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return $class === CustomerUser::class || is_subclass_of($class, CustomerUser::class);
    }
}
