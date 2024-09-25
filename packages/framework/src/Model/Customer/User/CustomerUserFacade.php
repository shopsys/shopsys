<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade $customerMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFactoryInterface $customerUserFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        protected readonly CustomerMailFacade $customerMailFacade,
        protected readonly CustomerUserFactoryInterface $customerUserFactory,
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        protected readonly CustomerFacade $customerFacade,
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly CustomerDataFactoryInterface $customerDataFactory,
        protected readonly BillingAddressFacade $billingAddressFacade,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly HashGenerator $hashGenerator,
    ) {
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserUpdateData $customerUserUpdateData)
    {
        $customer = $this->createCustomerWithAddresses($customerUserUpdateData);

        return $this->createCustomerUserWithRegistrationMail($customer, $customerUserUpdateData->customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function createWithActivationMail(CustomerUserUpdateData $customerUserUpdateData)
    {
        $customer = $this->createCustomerWithAddresses($customerUserUpdateData);

        return $this->createCustomerUserWithActivationMail($customer, $customerUserUpdateData->customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    protected function createCustomerUserWithRegistrationMail(
        Customer $customer,
        CustomerUserData $customerUserData,
    ): CustomerUser {
        $customerUserData->customer = $customer;
        $customerUser = $this->createCustomerUser($customer, $customerUserData);

        if ($customerUserData->sendRegistrationMail) {
            $this->customerMailFacade->sendRegistrationMail($customerUser);
        }

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function createCustomerUserWithActivationMail(
        Customer $customer,
        CustomerUserData $customerUserData,
    ): CustomerUser {
        $customerUserData->customer = $customer;
        $customerUser = $this->createCustomerUser($customer, $customerUserData);

        if ($customerUserData->sendRegistrationMail) {
            $this->sendActivationMail($customerUser);
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
        CustomerUserData $customerUserData,
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
    public function edit(
        int $customerUserId,
        CustomerUserUpdateData $customerUserUpdateData,
        ?DeliveryAddress $deliveryAddress = null,
    ) {
        $customerUser = $this->getCustomerUserById($customerUserId);
        $customerUserOriginalRoles = $customerUser->getRoles();

        if (
            $customerUserUpdateData->deliveryAddressData
            && $customerUserUpdateData->deliveryAddressData->addressFilled
        ) {
            $customerUserUpdateData->deliveryAddressData->customer = $customerUser->getCustomer();
            $deliveryAddress = $this->deliveryAddressFacade->createIfAddressFilled($customerUserUpdateData->deliveryAddressData);
            $customerUserUpdateData->customerUserData->defaultDeliveryAddress = $deliveryAddress;
        }

        if ($deliveryAddress !== null) {
            $customerUserUpdateData->customerUserData->defaultDeliveryAddress = $deliveryAddress;
        }

        $customerUser->edit($customerUserUpdateData->customerUserData);

        if ($customerUserUpdateData->customerUserData->password !== null) {
            $this->customerUserPasswordFacade->changePassword(
                $customerUser,
                $customerUserUpdateData->customerUserData->password,
            );
        }

        $this->billingAddressFacade->edit(
            $customerUser->getCustomer()->getBillingAddress()->getId(),
            $customerUserUpdateData->billingAddressData,
        );

        if ($customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->addSubscribedEmailIfNotExists($customerUser->getEmail(), $customerUser->getDomainId());
        } else {
            $this->newsletterFacade->deleteSubscribedEmailIfExists($customerUser->getEmail(), $customerUser->getDomainId());
        }

        if ($this->areRolesChanged($customerUser->getRoles(), $customerUserOriginalRoles)) {
            $this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser);
        }

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
    public function amendCustomerUserDataFromOrder(
        CustomerUser $customerUser,
        Order $order,
        ?DeliveryAddress $deliveryAddress,
    ) {
        $this->edit(
            $customerUser->getId(),
            $this->customerUserUpdateDataFactory->createAmendedByOrder($customerUser, $order, $deliveryAddress),
            $deliveryAddress,
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
            $customerUser->getDomainId(),
        );

        if (
            $customerUserByEmailAndDomain !== null
            && $customerUser->getId() !== $customerUserByEmailAndDomain->getId()
        ) {
            throw new DuplicateEmailException($email);
        }

        $customerUser->setEmail($email);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function createCustomerWithBillingAddress(
        int $domainId,
        BillingAddressData $billingAddressData,
    ): Customer {
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
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null $administrator
     */
    public function addRefreshTokenChain(
        CustomerUser $customerUser,
        string $refreshTokenChain,
        string $deviceId,
        DateTime $tokenExpiration,
        ?Administrator $administrator,
    ): void {
        $refreshTokenChain = $this->customerUserRefreshTokenChainFacade->createCustomerUserRefreshTokenChain(
            $customerUser,
            $refreshTokenChain,
            $deviceId,
            $tokenExpiration,
            $administrator,
        );
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string|null $deliveryAddressUuid
     * @param bool $isSubscribeToNewsletter
     */
    public function updateCustomerUserByOrder(
        CustomerUser $customerUser,
        Order $order,
        ?string $deliveryAddressUuid,
        bool $isSubscribeToNewsletter,
    ): void {
        $deliveryAddress = $this->resolveDeliveryAddress($deliveryAddressUuid, $customerUser);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserUpdateData->customerUserData->newsletterSubscription = $isSubscribeToNewsletter;
        $this->editByCustomerUser($customerUser->getId(), $customerUserUpdateData);
        $this->amendCustomerUserDataFromOrder($customerUser, $order, $deliveryAddress);
    }

    /**
     * @param string|null $deliveryAddressUuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    protected function resolveDeliveryAddress(
        ?string $deliveryAddressUuid,
        CustomerUser $customerUser,
    ): ?DeliveryAddress {
        if ($deliveryAddressUuid === null) {
            return null;
        }

        return $this->deliveryAddressFacade->findByUuidAndCustomer(
            $deliveryAddressUuid,
            $customerUser->getCustomer(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function sendActivationMail(CustomerUser $customerUser): void
    {
        $resetPasswordHash = $this->hashGenerator->generateHash(CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
        $customerUser->setResetPasswordHash($resetPasswordHash);
        $this->em->flush();

        $this->customerMailFacade->sendActivationMail($customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     */
    public function setDefaultDeliveryAddress(CustomerUser $customerUser, DeliveryAddress $deliveryAddress): void
    {
        $customerUser->setDefaultDeliveryAddress($deliveryAddress);
        $this->em->flush();
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editCustomerUser(int $id, CustomerUserData $customerUserData): CustomerUser
    {
        $customerUser = $this->getCustomerUserById($id);
        $customerUserOriginalRoles = $customerUser->getRoles();

        $customerUser->edit($customerUserData);

        $this->em->flush();

        if ($this->areRolesChanged($customerUser->getRoles(), $customerUserOriginalRoles)) {
            $this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser);
        }

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    protected function createCustomerWithAddresses(CustomerUserUpdateData $customerUserUpdateData): Customer
    {
        $customer = $this->createCustomerWithBillingAddress(
            $customerUserUpdateData->customerUserData->domainId,
            $customerUserUpdateData->billingAddressData,
        );

        if (
            $customerUserUpdateData->deliveryAddressData
            && $customerUserUpdateData->deliveryAddressData->addressFilled
        ) {
            $customerUserUpdateData->deliveryAddressData->customer = $customer;
            $deliveryAddress = $this->deliveryAddressFacade->createIfAddressFilled(
                $customerUserUpdateData->deliveryAddressData,
            );

            $customerData = $this->customerDataFactory->createFromCustomer($customer);

            if ($deliveryAddress !== null) {
                $customerData->deliveryAddresses[] = $deliveryAddress;
            }
            $this->customerFacade->edit($customer->getId(), $customerData);

            $customerUserUpdateData->customerUserData->defaultDeliveryAddress = $deliveryAddress;
        }

        return $customer;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser[]
     */
    public function getAll(): array
    {
        return $this->customerUserRepository->getAll();
    }

    /**
     * @param string $customerUserUuid
     * @param \DateTimeInterface $referenceDateTime
     * @return bool
     */
    public function isLastSecurityChangeOlderThan(string $customerUserUuid, DateTimeInterface $referenceDateTime): bool
    {
        return $this->customerUserRepository->isLastSecurityChangeOlderThan($customerUserUuid, $referenceDateTime);
    }

    /**
     * @param string[] $customerUserCurrentRoles
     * @param string[] $customerUserOriginalRoles
     * @return bool
     */
    protected function areRolesChanged(array $customerUserCurrentRoles, array $customerUserOriginalRoles): bool
    {
        return array_diff($customerUserCurrentRoles, $customerUserOriginalRoles) !== [] || array_diff($customerUserOriginalRoles, $customerUserCurrentRoles) !== [];
    }

    /**
     * @param int $salesRepresentativeId
     * @return string[]
     */
    public function findEmailsOfCustomerUsersUsingSalesRepresentative(int $salesRepresentativeId): array
    {
        return $this->customerUserRepository->findEmailsOfCustomerUsersUsingSalesRepresentative($salesRepresentativeId);
    }
}
