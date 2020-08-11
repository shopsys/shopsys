<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     */
    protected $customerUserUpdateDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade
     */
    protected $customerMailFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface
     */
    protected $billingAddressFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface
     */
    protected $billingAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface
     */
    protected $customerUserFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade
     */
    protected $customerUserPasswordFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     */
    protected $customerFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade
     */
    protected $deliveryAddressFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface
     */
    protected $customerDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade
     */
    protected $billingAddressFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade
     */
    protected $customerUserRefreshTokenChainFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CustomerUserRepository $customerUserRepository,
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CustomerMailFacade $customerMailFacade,
        BillingAddressFactoryInterface $billingAddressFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        CustomerUserFactoryInterface $customerUserFactory,
        CustomerUserPasswordFacade $customerUserPasswordFacade,
        CustomerFacade $customerFacade,
        DeliveryAddressFacade $deliveryAddressFacade,
        CustomerDataFactoryInterface $customerDataFactory,
        BillingAddressFacade $billingAddressFacade,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
    ) {
        $this->em = $em;
        $this->customerUserRepository = $customerUserRepository;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerMailFacade = $customerMailFacade;
        $this->billingAddressFactory = $billingAddressFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->customerUserFactory = $customerUserFactory;
        $this->customerUserPasswordFacade = $customerUserPasswordFacade;
        $this->customerFacade = $customerFacade;
        $this->deliveryAddressFacade = $deliveryAddressFacade;
        $this->customerDataFactory = $customerDataFactory;
        $this->billingAddressFacade = $billingAddressFacade;
        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
    }

    /**
     * @param int $customerUserId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function getCustomerUserById($customerUserId)
    {
        return $this->customerUserRepository->getCustomerUserById($customerUserId);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findCustomerUserByEmailAndDomain($email, $domainId)
    {
        return $this->customerUserRepository->findCustomerUserByEmailAndDomain($email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function register(CustomerUserData $customerUserData)
    {
        $customer = $this->createCustomerWithBillingAddress($customerUserData->domainId, $this->billingAddressDataFactory->create());

        $customerUser = $this->createCustomerUser($customer, $customerUserData);

        $this->customerMailFacade->sendRegistrationMail($customerUser);

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserUpdateData $customerUserUpdateData)
    {
        $customer = $this->createCustomerWithBillingAddress($customerUserUpdateData->customerUserData->domainId, $customerUserUpdateData->billingAddressData);

        if ($customerUserUpdateData->deliveryAddressData && $customerUserUpdateData->deliveryAddressData->addressFilled) {
            $customerUserUpdateData->deliveryAddressData->customer = $customer;
            $deliveryAddress = $this->deliveryAddressFacade->create($customerUserUpdateData->deliveryAddressData);

            $customerData = $this->customerDataFactory->createFromCustomer($customer);
            $customerData->deliveryAddresses[] = $deliveryAddress;
            $this->customerFacade->edit($customer->getId(), $customerData);

            $customerUserUpdateData->customerUserData->defaultDeliveryAddress = $deliveryAddress;
        }

        $customerUser = $this->createCustomerUser($customer, $customerUserUpdateData->customerUserData);

        if ($customerUserUpdateData->sendRegistrationMail) {
            $this->customerMailFacade->sendRegistrationMail($customerUser);
        }

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    protected function createCustomerUser(
        Customer $customer,
        CustomerUserData $customerUserData
    ): CustomerUser {
        $customerUserData->customer = $customer;
        $customerUser = $this->customerUserFactory->create($customerUserData);
        $this->setEmail($customerUserData->email, $customerUser);

        $this->em->persist($customerUser);
        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    protected function edit(
        int $customerUserId,
        CustomerUserUpdateData $customerUserUpdateData,
        ?DeliveryAddress $deliveryAddress = null
    ) {
        $customerUser = $this->getCustomerUserById($customerUserId);
        $customerUserUpdateData->deliveryAddressData->customer = $customerUser->getCustomer();

        if ($customerUserUpdateData->deliveryAddressData && $customerUserUpdateData->deliveryAddressData->addressFilled) {
            $deliveryAddress = $this->deliveryAddressFacade->create($customerUserUpdateData->deliveryAddressData);
            $customerUserUpdateData->customerUserData->defaultDeliveryAddress = $deliveryAddress;
        }

        if ($deliveryAddress !== null) {
            $customerUserUpdateData->customerUserData->defaultDeliveryAddress = $deliveryAddress;
        }

        $customerUser->edit($customerUserUpdateData->customerUserData);

        if ($customerUserUpdateData->customerUserData->password !== null) {
            $this->customerUserPasswordFacade->changePassword($customerUser, $customerUserUpdateData->customerUserData->password);
        }

        $this->billingAddressFacade->edit($customerUser->getCustomer()->getBillingAddress()->getId(), $customerUserUpdateData->billingAddressData);

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editByAdmin(int $customerUserId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $customerUser = $this->edit($customerUserId, $customerUserUpdateData);

        $this->setEmail($customerUserUpdateData->customerUserData->email, $customerUser);

        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editByCustomerUser(int $customerUserId, CustomerUserUpdateData $customerUserUpdateData)
    {
        $customerUser = $this->edit($customerUserId, $customerUserUpdateData);

        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param int $customerUserId
     */
    public function delete($customerUserId)
    {
        $customerUser = $this->getCustomerUserById($customerUserId);

        $this->em->remove($customerUser);
        $this->em->flush();

        $this->customerFacade->deleteIfNoCustomerUsersLeft($customerUser->getCustomer());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    public function amendCustomerUserDataFromOrder(CustomerUser $customerUser, Order $order, ?DeliveryAddress $deliveryAddress)
    {
        $this->edit(
            $customerUser->getId(),
            $this->customerUserUpdateDataFactory->createAmendedByOrder($customerUser, $order, $deliveryAddress),
            $deliveryAddress
        );

        $this->em->flush();
    }

    /**
     * @param string $email
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    protected function setEmail(string $email, CustomerUser $customerUser): void
    {
        $customerUserByEmailAndDomain = $this->findCustomerUserByEmailAndDomain(
            $email,
            $customerUser->getDomainId()
        );

        if ($customerUserByEmailAndDomain !== null && $customerUser->getId() !== $customerUserByEmailAndDomain->getId()) {
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException($email);
        }

        $customerUser->setEmail($email);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    protected function createCustomerWithBillingAddress(int $domainId, BillingAddressData $billingAddressData): Customer
    {
        $customerData = $this->customerDataFactory->createForDomain($domainId);
        $customer = $this->customerFacade->create($customerData);

        $billingAddressData->customer = $customer;
        $billingAddress = $this->billingAddressFacade->create($billingAddressData);

        $customerData->billingAddress = $billingAddress;
        $this->customerFacade->edit($customer->getId(), $customerData);

        return $customer;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $refreshTokenChain
     * @param string $deviceId
     * @param \DateTime $tokenExpiration
     */
    public function addRefreshTokenChain(CustomerUser $customerUser, string $refreshTokenChain, string $deviceId, \DateTime $tokenExpiration): void
    {
        $refreshTokenChain = $this->customerUserRefreshTokenChainFacade->createCustomerUserRefreshTokenChain($customerUser, $refreshTokenChain, $deviceId, $tokenExpiration);
        $customerUser->addRefreshTokenChain($refreshTokenChain);
        $this->em->flush();
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function getByUuid(string $uuid): CustomerUser
    {
        return $this->customerUserRepository->getOneByUuid($uuid);
    }
}
