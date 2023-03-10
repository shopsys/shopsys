<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Model\Administrator\Administrator;
use DateTime;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade as BaseCustomerUserRefreshTokenChainFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory, \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory, \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository)
 * @method removeAllCustomerUserRefreshTokenChains(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class CustomerUserRefreshTokenChainFacade extends BaseCustomerUserRefreshTokenChainFacade
{
    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \App\Model\Customer\User\CustomerUserRefreshTokenChain|null
     * @deprecated Method is deprecated. Use "findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId()" instead.
     */
    public function findCustomersTokenChainByCustomerUserAndSecretChain(CustomerUser $customerUser, string $secretChain): ?CustomerUserRefreshTokenChain
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @param string $deviceId
     * @return \App\Model\Customer\User\CustomerUserRefreshTokenChain|null
     */
    public function findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(
        CustomerUser $customerUser,
        string $secretChain,
        string $deviceId
    ): ?CustomerUserRefreshTokenChain {
        $encoder = $this->passwordHasherFactory->getPasswordHasher($customerUser);
        $customersTokenChains = $this->customerUserRefreshTokenChainRepository->findCustomersTokenChainsByDeviceId(
            $customerUser,
            $deviceId
        );

        foreach ($customersTokenChains as $customersTokenChain) {
            if ($encoder->verify($customersTokenChain->getTokenChain(), $secretChain)) {
                return $customersTokenChain;
            }
        }

        return null;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain
     */
    public function removeCustomerRefreshTokenChain(CustomerUserRefreshTokenChain $refreshTokenChain): void
    {
        $this->customerUserRefreshTokenChainRepository->removeCustomerRefreshTokenChain($refreshTokenChain);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $tokenChain
     * @param string $deviceId
     * @param \DateTime $tokenExpiration
     * @param \App\Model\Administrator\Administrator|null $administrator
     * @return \App\Model\Customer\User\CustomerUserRefreshTokenChain
     */
    public function createCustomerUserRefreshTokenChain(
        CustomerUser $customerUser,
        string $tokenChain,
        string $deviceId,
        DateTime $tokenExpiration,
        ?Administrator $administrator = null
    ): CustomerUserRefreshTokenChain {
        /** @var \App\Model\Customer\User\CustomerUserRefreshTokenChain $customerUserRefreshTokenChain */
        $customerUserRefreshTokenChain = parent::createCustomerUserRefreshTokenChain(
            $customerUser,
            $tokenChain,
            $deviceId,
            $tokenExpiration
        );

        $customerUserRefreshTokenChain->setAdministrator($administrator);

        return $customerUserRefreshTokenChain;
    }
}
