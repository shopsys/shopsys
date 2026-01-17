<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class CustomerUserRefreshTokenChainFacade
{
    public function __construct(
        protected readonly CustomerUserRefreshTokenChainDataFactory $customerUserRefreshTokenChainDataFactory,
        protected readonly CustomerUserRefreshTokenChainFactory $customerUserRefreshTokenChainFactory,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository,
    ) {
    }

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

    public function removeCustomerUserRefreshTokenChainsByDeviceId(string $deviceId): void
    {
        $this->customerUserRefreshTokenChainRepository->removeCustomerUserRefreshTokenChainsByDeviceId($deviceId);
    }

    public function removeAllCustomerUserRefreshTokenChains(
        CustomerUser $customerUser,
        ?string $excludedDeviceId = null,
    ): void {
        $this->customerUserRefreshTokenChainRepository->removeAllCustomerUserRefreshTokenChains($customerUser, $excludedDeviceId);
    }

    public function removeCustomerRefreshTokenChain(CustomerUserRefreshTokenChain $refreshTokenChain): void
    {
        $this->customerUserRefreshTokenChainRepository->removeCustomerRefreshTokenChain($refreshTokenChain);
    }
}
