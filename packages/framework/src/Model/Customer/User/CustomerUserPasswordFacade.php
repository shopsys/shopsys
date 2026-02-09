<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashUserException;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class CustomerUserPasswordFacade
{
    public const RESET_PASSWORD_HASH_LENGTH = 50;
    public const MINIMUM_PASSWORD_LENGTH = 6;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly ResetPasswordMailFacade $resetPasswordMailFacade,
        protected readonly HashGenerator $hashGenerator,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function resetPassword(string $email, int $domainId): void
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        $resetPasswordHash = $this->hashGenerator->generateHash(static::RESET_PASSWORD_HASH_LENGTH);
        $customerUser->setResetPasswordHash($resetPasswordHash);

        $this->em->flush();
        $this->resetPasswordMailFacade->sendMail($customerUser);
    }

    public function isResetPasswordHashValid(string $email, int $domainId, ?string $hash): bool
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        return $customerUser->isResetPasswordHashValid($hash);
    }

    public function setNewPassword(
        string $email,
        int $domainId,
        ?string $resetPasswordHash,
        string $newPassword,
    ): CustomerUser {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        if (!$customerUser->isResetPasswordHashValid($resetPasswordHash)) {
            throw new InvalidResetPasswordHashUserException();
        }

        $this->changePassword($customerUser, $newPassword);

        return $customerUser;
    }

    public function changePassword(CustomerUser $customerUser, string $password, ?string $deviceId = null): void
    {
        $this->setPassword($customerUser, $password);

        $this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser, $deviceId);
    }

    public function setPassword(CustomerUser $customerUser, string $password): void
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($customerUser);
        $passwordHash = $passwordHasher->hash($password);
        $customerUser->setPasswordHash($passwordHash);

        $this->em->flush();
    }
}
