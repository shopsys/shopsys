<?php

declare(strict_types=1);


namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade as BaseCustomerUserPasswordFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserRepository $customerUserRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Customer\User\CustomerUserRepository $customerUserRepository, \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory, \Shopsys\FrameworkBundle\Model\Customer\Mail\ResetPasswordMailFacade $resetPasswordMailFacade, \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade)
 * @method \App\Model\Customer\User\CustomerUser setNewPassword(string $email, int $domainId, string|null $resetPasswordHash, string $newPassword)
 */
class CustomerUserPasswordFacade extends BaseCustomerUserPasswordFacade
{
    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $password
     */
    public function changePassword(CustomerUser $customerUser, string $password): void
    {
        if ($password !== '') {
            parent::changePassword($customerUser, $password);
        } else {
            $customerUser->setPasswordHash('');
        }
    }
}
