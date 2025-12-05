<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class CustomerUserRefreshTokenChainFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainDataFactory $customerUserRefreshTokenChainDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFactory $customerUserRefreshTokenChainFactory
     * @param \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
     */
    public function __construct(
        protected readonly CustomerUserRefreshTokenChainDataFactory $customerUserRefreshTokenChainDataFactory,
        protected readonly CustomerUserRefreshTokenChainFactory $customerUserRefreshTokenChainFactory,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $tokenChain
     * @param string $deviceId
     * @param \DateTimeImmutable $tokenExpiration
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null $administrator
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain
     */
    public function createCustomerUserRefreshTokenChain(
        CustomerUser $customerUser,
        string $tokenChain,
        string $deviceId,
        DateTimeImmutable $tokenExpiration,
        ?Administrator $administrator,
    ): CustomerUserRefreshTokenChain {
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($customerUser);

        $customerUserRefreshTokenChainData = $this->customerUserRefreshTokenChainDataFactory->create();
        $customerUserRefreshTokenChainData->customerUser = $customerUser;
        $customerUserRefreshTokenChainData->tokenChain = $passwordHasher->hash($tokenChain);
        $customerUserRefreshTokenChainData->deviceId = $deviceId;
        $customerUserRefreshTokenChainData->expiredAt = $tokenExpiration;
        $customerUserRefreshTokenChainData->administrator = $administrator;

        return $this->customerUserRefreshTokenChainFactory->create($customerUserRefreshTokenChainData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @param string $deviceId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain|null
     */
    public function findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(
        CustomerUser $customerUser,
        string $secretChain,
        string $deviceId,
    ): ?CustomerUserRefreshTokenChain {
        $encoder = $this->passwordHasherFactory->getPasswordHasher($customerUser);
        $customersTokenChains = $this->customerUserRefreshTokenChainRepository->findCustomersTokenChainsByDeviceId(
            $customerUser,
            $deviceId,
        );

        foreach ($customersTokenChains as $customersTokenChain) {
            if ($encoder->verify($customersTokenChain->getTokenChain(), $secretChain)) {
                return $customersTokenChain;
            }
        }

        return null;
    }

    /**
     * @param string $deviceId
     */
    public function removeCustomerUserRefreshTokenChainsByDeviceId(string $deviceId): void
    {
        $this->customerUserRefreshTokenChainRepository->removeCustomerUserRefreshTokenChainsByDeviceId($deviceId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string|null $excludedDeviceId
     */
    public function removeAllCustomerUserRefreshTokenChains(
        CustomerUser $customerUser,
        ?string $excludedDeviceId = null,
    ): void {
        $this->customerUserRefreshTokenChainRepository->removeAllCustomerUserRefreshTokenChains($customerUser, $excludedDeviceId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain $refreshTokenChain
     */
    public function removeCustomerRefreshTokenChain(CustomerUserRefreshTokenChain $refreshTokenChain): void
    {
        $this->customerUserRefreshTokenChainRepository->removeCustomerRefreshTokenChain($refreshTokenChain);
    }
}
