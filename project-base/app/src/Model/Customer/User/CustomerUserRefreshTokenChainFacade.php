<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade as BaseCustomerUserRefreshTokenChainFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory, \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory, \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository)
 * @method removeAllCustomerUserRefreshTokenChains(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\User\CustomerUserRefreshTokenChain createCustomerUserRefreshTokenChain(\App\Model\Customer\User\CustomerUser $customerUser, string $tokenChain, string $deviceId, \DateTime $tokenExpiration, \App\Model\Administrator\Administrator|null $administrator)
 * @method \App\Model\Customer\User\CustomerUserRefreshTokenChain|null findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(\App\Model\Customer\User\CustomerUser $customerUser, string $secretChain, string $deviceId)
 * @method removeCustomerRefreshTokenChain(\App\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain)
 */
class CustomerUserRefreshTokenChainFacade extends BaseCustomerUserRefreshTokenChainFacade
{
}
