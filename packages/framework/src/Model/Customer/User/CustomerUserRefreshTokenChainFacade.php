<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class CustomerUserRefreshTokenChainFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainDataFactoryInterface
     */
    protected $customerUserRefreshTokenChainDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFactoryInterface
     */
    protected $customerUserRefreshTokenChainFactory;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository
     */
    protected $customerUserRefreshTokenChainRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
     */
    public function __construct(
        CustomerUserRefreshTokenChainDataFactoryInterface $customerUserRefreshTokenChainDataFactory,
        CustomerUserRefreshTokenChainFactoryInterface $customerUserRefreshTokenChainFactory,
        EncoderFactoryInterface $encoderFactory,
        CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
    ) {
        $this->customerUserRefreshTokenChainDataFactory = $customerUserRefreshTokenChainDataFactory;
        $this->customerUserRefreshTokenChainFactory = $customerUserRefreshTokenChainFactory;
        $this->encoderFactory = $encoderFactory;
        $this->customerUserRefreshTokenChainRepository = $customerUserRefreshTokenChainRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $tokenChain
     * @param string $deviceId
     * @param \DateTime $tokenExpiration
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain
     */
    public function createCustomerUserRefreshTokenChain(CustomerUser $customerUser, string $tokenChain, string $deviceId, \DateTime $tokenExpiration): CustomerUserRefreshTokenChain
    {
        $encoder = $this->encoderFactory->getEncoder($customerUser);

        $customerUserRefreshTokenChainData = $this->customerUserRefreshTokenChainDataFactory->create();
        $customerUserRefreshTokenChainData->customerUser = $customerUser;
        $customerUserRefreshTokenChainData->tokenChain = $encoder->encodePassword($tokenChain, null);
        $customerUserRefreshTokenChainData->deviceId = $deviceId;
        $customerUserRefreshTokenChainData->expiredAt = $tokenExpiration;

        return $this->customerUserRefreshTokenChainFactory->create($customerUserRefreshTokenChainData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain|null
     */
    public function findCustomersTokenChainByCustomerUserAndSecretChain(CustomerUser $customerUser, string $secretChain): ?CustomerUserRefreshTokenChain
    {
        $encoder = $this->encoderFactory->getEncoder($customerUser);
        $customersTokenChains = $this->customerUserRefreshTokenChainRepository->findCustomersTokenChains($customerUser);

        foreach ($customersTokenChains as $customersTokenChain) {
            if ($encoder->isPasswordValid($customersTokenChain->getTokenChain(), $secretChain, null)) {
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
     */
    public function removeAllCustomerUserRefreshTokenChains(CustomerUser $customerUser): void
    {
        $this->customerUserRefreshTokenChainRepository->removeAllCustomerUserRefreshTokenChains($customerUser);
    }
}
