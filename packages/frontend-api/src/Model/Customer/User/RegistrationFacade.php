<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory as FrameworkCustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Order\Exception\RegisterByOrderIsNotPossibleUserError;

class RegistrationFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $frameworkCustomerUserUpdateDataFactory
     */
    public function __construct(
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly Domain $domain,
        protected readonly OrderFacade $orderFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly FrameworkCustomerUserUpdateDataFactory $frameworkCustomerUserUpdateDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData $registrationData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function register(RegistrationData $registrationData): CustomerUser
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($registrationData->email, $this->domain->getId());

        if ($customerUser !== null) {
            if ($customerUser->isActivated() === true) {
                throw new DuplicateEmailException($registrationData->email);
            }

            $customerUserUpdateData = $this->mapRegistrationDataToCustomerUserUpdateData($customerUser, $registrationData);
            $this->customerUserFacade->edit($customerUser->getId(), $customerUserUpdateData);
            $this->customerUserFacade->sendActivationMail($customerUser);

            return $customerUser;
        }

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromRegistrationData($registrationData);

        return $this->customerUserFacade->create($customerUserUpdateData);
    }

    /**
     * @param string $orderUrlHash
     * @param string $password
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function registerByOrder(string $orderUrlHash, string $password): CustomerUser
    {
        try {
            $order = $this->orderFacade->getByUrlHashAndDomain($orderUrlHash, $this->domain->getId());
        } catch (OrderNotFoundException) {
            throw new RegisterByOrderIsNotPossibleUserError('Order not found.');
        }

        if ($order->getCustomerUser() !== null) {
            throw new RegisterByOrderIsNotPossibleUserError('Order is owned by another customer.');
        }

        if ($order->getCreatedAt() < new DateTime('-1 hour')) {
            throw new RegisterByOrderIsNotPossibleUserError('Registration for a established order is possible only within an hour of establishment of an order.');
        }

        $customerUserUpdateData = $this->frameworkCustomerUserUpdateDataFactory->createFromOrder($order, $password);
        $customerUser = $this->customerUserFacade->create($customerUserUpdateData);

        $order->setCustomerUser($customerUser);
        $this->em->flush();

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\RegistrationData $registrationData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    protected function mapRegistrationDataToCustomerUserUpdateData(
        CustomerUser $customerUser,
        RegistrationData $registrationData,
    ): CustomerUserUpdateData {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = $registrationData->companyCustomer;

        if ($registrationData->companyCustomer === true) {
            $billingAddressData->companyName = $registrationData->companyName;
            $billingAddressData->companyNumber = $registrationData->companyNumber;
            $billingAddressData->companyTaxNumber = $registrationData->companyTaxNumber;
        } else {
            $billingAddressData->companyName = null;
            $billingAddressData->companyNumber = null;
            $billingAddressData->companyTaxNumber = null;
        }
        $billingAddressData->street = $registrationData->street;
        $billingAddressData->city = $registrationData->city;
        $billingAddressData->postcode = $registrationData->postcode;
        $billingAddressData->country = $registrationData->country;

        $customerUserData = $customerUserUpdateData->customerUserData;
        $customerUserData->firstName = $registrationData->firstName;
        $customerUserData->lastName = $registrationData->lastName;
        $customerUserData->telephone = $registrationData->telephone;

        return $customerUserUpdateData;
    }
}
