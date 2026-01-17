<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException;
use Shopsys\FrameworkBundle\Model\Customer\Mail\CustomerMailFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerMailFacade $customerMailFacade,
        protected readonly CustomerUserFactory $customerUserFactory,
        protected readonly CustomerUserPasswordFacade $customerUserPasswordFacade,
        protected readonly CustomerFacade $customerFacade,
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly CustomerDataFactory $customerDataFactory,
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
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserUpdateData $customerUserUpdateData)
    {
        $customer = $this->createCustomerWithAddresses($customerUserUpdateData);

        return $this->createCustomerUserWithRegistrationMail($customer, $customerUserUpdateData->customerUserData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function createWithActivationMail(CustomerUserUpdateData $customerUserUpdateData)
    {
        $customer = $this->createCustomerWithAddresses($customerUserUpdateData);

        return $this->createCustomerUserWithActivationMail($customer, $customerUserUpdateData->customerUserData);
    }

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

    protected function createCustomerUser(
        Customer $customer,
        CustomerUserData $customerUserData,
    ): CustomerUser {
        $customerUserData->customer = $customer;
        $customerUser = $this->customerUserFactory->create($customerUserData);
        $this->setEmail($customerUserData->email, $customerUser);

        $this->em->persist($customerUser);
        $this->em->flush();

        if ($customerUserData->newsletterSubscription) {
            $this->newsletterFacade->addSubscribedEmailIfNotExists($customerUser->getEmail(), $customerUser->getDomainId());
        }

        return $customerUser;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function edit(
        int $customerUserId,
        CustomerUserUpdateData $customerUserUpdateData,
        ?DeliveryAddress $deliveryAddress = null,
        ?string $deviceId = null,
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
                $deviceId,
            );
        }

        $this->billingAddressFacade->edit(
            $customerUser->getCustomer()->getBillingAddress()->getId(),
            $customerUserUpdateData->billingAddressData,
        );

        if ($customerUserUpdateData->customerUserData->newsletterSubscription) {
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
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function editByCustomerUser(
        int $customerUserId,
        CustomerUserUpdateData $customerUserUpdateData,
        ?string $deviceId = null,
    ) {
        $customerUser = $this->edit($customerUserId, $customerUserUpdateData, null, $deviceId);

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

    public function addRefreshTokenChain(
        CustomerUser $customerUser,
        string $refreshTokenChain,
        string $deviceId,
        DateTimeImmutable $tokenExpiration,
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

    public function getByUuid(string $uuid): CustomerUser
    {
        return $this->customerUserRepository->getOneByUuid($uuid);
    }

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

    public function sendActivationMail(CustomerUser $customerUser): void
    {
        $resetPasswordHash = $this->hashGenerator->generateHash(CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
        $customerUser->setResetPasswordHash($resetPasswordHash);
        $this->em->flush();

        $this->customerMailFacade->sendActivationMail($customerUser);
    }

    public function setDefaultDeliveryAddress(CustomerUser $customerUser, DeliveryAddress $deliveryAddress): void
    {
        $customerUser->setDefaultDeliveryAddress($deliveryAddress);
        $this->em->flush();
    }

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

    public function isLastSecurityChangeOlderThan(string $customerUserUuid, DateTimeInterface $referenceDateTime): bool
    {
        return $this->customerUserRepository->isLastSecurityChangeOlderThan($customerUserUuid, $referenceDateTime);
    }

    /**
     * @param string[] $customerUserCurrentRoles
     * @param string[] $customerUserOriginalRoles
     */
    protected function areRolesChanged(array $customerUserCurrentRoles, array $customerUserOriginalRoles): bool
    {
        return ArrayHelper::haveArraysDifferentValues($customerUserCurrentRoles, $customerUserOriginalRoles);
    }

    /**
     * @return string[]
     */
    public function findEmailsOfCustomerUsersUsingSalesRepresentative(int $salesRepresentativeId): array
    {
        return $this->customerUserRepository->findEmailsOfCustomerUsersUsingSalesRepresentative($salesRepresentativeId);
    }
}
