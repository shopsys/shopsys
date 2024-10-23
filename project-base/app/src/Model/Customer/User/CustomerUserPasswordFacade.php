<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade as BaseCustomerUserPasswordFacade;

/**
 * @property \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository, \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory, \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade $resetPasswordMailFacade, \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator, \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade)
 * @method changePassword(\App\Model\Customer\User\CustomerUser $customerUser, string $password, string|null $deviceId = null)
 * @method setPassword(\App\Model\Customer\User\CustomerUser $customerUser, string $password)
 */
class CustomerUserPasswordFacade extends BaseCustomerUserPasswordFacade
{
    /**
     * @param string $email
     * @param int $domainId
     * @param string|null $resetPasswordHash
     * @param string $newPassword
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function setNewPassword(
        string $email,
        int $domainId,
        ?string $resetPasswordHash,
        string $newPassword,
    ): CustomerUser {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = parent::setNewPassword($email, $domainId, $resetPasswordHash, $newPassword);
        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();

        $billingAddress->activate();
        $this->em->flush();

        return $customerUser;
    }
}
