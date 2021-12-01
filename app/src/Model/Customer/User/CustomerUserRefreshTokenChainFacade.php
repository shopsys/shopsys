<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use App\FrontendApi\Exception\DeprecatedMethodException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade as BaseCustomerUserRefreshTokenChainFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory, \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory, \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository)
 * @method \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain createCustomerUserRefreshTokenChain(\App\Model\Customer\User\CustomerUser $customerUser, string $tokenChain, string $deviceId, \DateTime $tokenExpiration)
 * @method removeAllCustomerUserRefreshTokenChains(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class CustomerUserRefreshTokenChainFacade extends BaseCustomerUserRefreshTokenChainFacade
{
    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain|null
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
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain|null
     */
    public function findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(
        CustomerUser $customerUser,
        string $secretChain,
        string $deviceId
    ): ?CustomerUserRefreshTokenChain {
        $encoder = $this->encoderFactory->getEncoder($customerUser);
        $customersTokenChains = $this->customerUserRefreshTokenChainRepository->findCustomersTokenChainsByDeviceId(
            $customerUser,
            $deviceId
        );

        foreach ($customersTokenChains as $customersTokenChain) {
            if ($encoder->isPasswordValid($customersTokenChain->getTokenChain(), $secretChain, null)) {
                return $customersTokenChain;
            }
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain
     */
    public function removeCustomerRefreshTokenChain(CustomerUserRefreshTokenChain $refreshTokenChain): void
    {
        $this->customerUserRefreshTokenChainRepository->removeCustomerRefreshTokenChain($refreshTokenChain);
    }
}
