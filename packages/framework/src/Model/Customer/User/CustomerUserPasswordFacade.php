<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class CustomerUserPasswordFacade
{
    public const RESET_PASSWORD_HASH_LENGTH = 50;
    public const MINIMUM_PASSWORD_LENGTH = 6;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade
     */
    protected $resetPasswordMailFacade;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    protected $hashGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade
     */
    protected $customerUserRefreshTokenChainFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade $resetPasswordMailFacade
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CustomerUserRepository $customerUserRepository,
        EncoderFactoryInterface $encoderFactory,
        ResetPasswordMailFacade $resetPasswordMailFacade,
        HashGenerator $hashGenerator,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
    ) {
        $this->em = $em;
        $this->customerUserRepository = $customerUserRepository;
        $this->encoderFactory = $encoderFactory;
        $this->resetPasswordMailFacade = $resetPasswordMailFacade;
        $this->hashGenerator = $hashGenerator;
        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function resetPassword($email, $domainId)
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        $resetPasswordHash = $this->hashGenerator->generateHash(static::RESET_PASSWORD_HASH_LENGTH);
        $customerUser->setResetPasswordHash($resetPasswordHash);

        $this->em->flush($customerUser);
        $this->resetPasswordMailFacade->sendMail($customerUser);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @param string|null $hash
     * @return bool
     */
    public function isResetPasswordHashValid($email, $domainId, $hash)
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        return $customerUser->isResetPasswordHashValid($hash);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @param string|null $resetPasswordHash
     * @param string $newPassword
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function setNewPassword(string $email, int $domainId, ?string $resetPasswordHash, string $newPassword): CustomerUser
    {
        $customerUser = $this->customerUserRepository->getCustomerUserByEmailAndDomain($email, $domainId);

        if (!$customerUser->isResetPasswordHashValid($resetPasswordHash)) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashUserException();
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
        $encoder = $this->encoderFactory->getEncoder($customerUser);
        $passwordHash = $encoder->encodePassword($password, null);
        $customerUser->setPasswordHash($passwordHash);

        $this->em->flush();

        $this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser);
    }
}
