<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository as BaseCustomerUserRefreshTokenChainRepository;

/**
 * @method \App\Model\Customer\User\CustomerUserRefreshTokenChain[] findCustomersTokenChains(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method void removeAllCustomerUserRefreshTokenChains(\App\Model\Customer\User\CustomerUser $customerUser, string|null $excludedDeviceId = null)
 * @method void removeCustomerRefreshTokenChain(\App\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain)
 * @method array findCustomersTokenChainsByDeviceId(\App\Model\Customer\User\CustomerUser $customerUser, string $deviceId)
 */
class CustomerUserRefreshTokenChainRepository extends BaseCustomerUserRefreshTokenChainRepository
{
}
