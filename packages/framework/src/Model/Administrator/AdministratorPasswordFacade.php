<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\InvalidResetPasswordHashAdministratorException;
use Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMailFacade;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AdministratorPasswordFacade
{
    public const RESET_PASSWORD_HASH_LENGTH = 50;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository $administratorRepository
     * @param \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Mail\ResetPasswordMailFacade $resetPasswordMailFacade
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorRepository $administratorRepository,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly ResetPasswordMailFacade $resetPasswordMailFacade,
        protected readonly HashGenerator $hashGenerator,
    ) {
    }

    /**
     * @param string $administratorUserName
     */
    public function resetPassword(string $administratorUserName): void
    {
        $administrator = $this->administratorRepository->getByUserName($administratorUserName);

        $resetPasswordHash = $this->hashGenerator->generateHash(static::RESET_PASSWORD_HASH_LENGTH);
        $administrator->setResetPasswordHash($resetPasswordHash);

        $this->em->flush();

        $this->resetPasswordMailFacade->sendMail($administrator);
    }

    /**
     * @param string $administratorUserName
     * @param string|null $resetPasswordHash
     * @param string $newPassword
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function setNewPassword(
        string $administratorUserName,
        ?string $resetPasswordHash,
        string $newPassword,
    ): Administrator {
        $administrator = $this->administratorRepository->getByUserName($administratorUserName);

        if (!$administrator->isResetPasswordHashValid($resetPasswordHash)) {
            throw new InvalidResetPasswordHashAdministratorException();
        }

        $this->setPassword($administrator, $newPassword);

        return $administrator;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $password
     */
    public function setPassword(Administrator $administrator, string $password): void
    {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($administrator);
        $passwordHash = $passwordHasher->hash($password);
        $administrator->setPasswordHash($passwordHash);

        $this->em->flush();
    }
}
