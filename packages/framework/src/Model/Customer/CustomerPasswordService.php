<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use DateTime;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class CustomerPasswordService
{
    const RESET_PASSWORD_HASH_LENGTH = 50;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        HashGenerator $hashGenerator
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @param string $password
     */
    public function changePassword(User $user, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordHash = $encoder->encodePassword($password, $user->getSalt());
        $user->changePassword($passwordHash);
    }

    public function resetPassword(User $user)
    {
        $hash = $this->hashGenerator->generateHash(self::RESET_PASSWORD_HASH_LENGTH);
        $user->setResetPasswordHash($hash);
    }

    /**
     * @param string|null $hash
     */
    public function isResetPasswordHashValid(User $user, $hash): bool
    {
        if ($hash === null || $user->getResetPasswordHash() !== $hash) {
            return false;
        }

        $now = new DateTime();
        if ($user->getResetPasswordHashValidThrough() === null || $user->getResetPasswordHashValidThrough() < $now) {
            return false;
        }

        return true;
    }

    /**
     * @param string|null $hash
     * @param string $newPassword
     */
    public function setNewPassword(User $user, $hash, $newPassword)
    {
        if (!$this->isResetPasswordHashValid($user, $hash)) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashException();
        }

        $this->changePassword($user, $newPassword);
    }
}
