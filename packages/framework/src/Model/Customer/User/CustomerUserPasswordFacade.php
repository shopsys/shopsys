<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashUserException;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class CustomerUserPasswordFacade
{
    public const RESET_PASSWORD_HASH_LENGTH = 50;
    public const MINIMUM_PASSWORD_LENGTH = 6;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade $resetPasswordMailFacade
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly ResetPasswordMailFacade $resetPasswordMailFacade,
        protected readonly HashGenerator $hashGenerator,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
    ) {
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function resetPassword(string $email, int $domainId): void
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        $resetPasswordHash = $this->hashGenerator->generateHash(static::RESET_PASSWORD_HASH_LENGTH);
        $customerUser->setResetPasswordHash($resetPasswordHash);

        $this->em->flush();
        $this->resetPasswordMailFacade->sendMail($customerUser);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @param string|null $hash
     * @return bool
     */
    public function isResetPasswordHashValid(string $email, int $domainId, ?string $hash): bool
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        return $customerUser->isResetPasswordHashValid($hash);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @param string|null $resetPasswordHash
     * @param string $newPassword
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function setNewPassword(string $email, int $domainId, ?string $resetPasswordHash, string $newPassword): CustomerUser
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        if (!$customerUser->isResetPasswordHashValid($resetPasswordHash)) {
            throw new InvalidResetPasswordHashUserException();
        }

        $this->changePassword($customerUser, $newPassword);

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $password
     */
    public function changePassword(CustomerUser $customerUser, string $password): void
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($customerUser);
        $passwordHash = $passwordHasher->hash($password);
        $customerUser->setPasswordHash($passwordHash);

        $this->em->flush();

        $this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser);
    }
}
