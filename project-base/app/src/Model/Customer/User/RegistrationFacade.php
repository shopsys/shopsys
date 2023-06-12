<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class RegistrationFacade implements RegistrationFacadeInterface
{
    /**
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        private CustomerUserFacade $customerUserFacade,
        private NewsletterFacade $newsletterFacade,
        private Domain $domain,
    ) {
    }

    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function register(RegistrationData $registrationData): CustomerUser
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($registrationData->email, $this->domain->getId());

        if ($customerUser !== null) {
            if ($customerUser->isActivated() === true) {
                throw new DuplicateUserNameException($registrationData->email);
            }

            $customerUserUpdateData = $this->mapRegistrationDataToCustomerUserUpdateData($customerUser, $registrationData);
            $this->customerUserFacade->edit($customerUser->getId(), $customerUserUpdateData);
            $this->customerUserFacade->sendActivationMail($customerUser);

            return $customerUser;
        }

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromRegistrationData($registrationData);

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->create($customerUserUpdateData);

        if ($customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->addSubscribedEmail($customerUser->getEmail(), $customerUser->getDomainId());
        }

        return $customerUser;
    }

    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function registerCompany(RegistrationData $registrationData): CustomerUser
    {
        $registrationData->companyCustomer = true;
        return $this->register($registrationData);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUserUpdateData
     */
    private function mapRegistrationDataToCustomerUserUpdateData(
        CustomerUser $customerUser,
        RegistrationData $registrationData,
    ): CustomerUserUpdateData {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
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

        /** @var \App\Model\Customer\User\CustomerUserData $customerUserData */
        $customerUserData = $customerUserUpdateData->customerUserData;
        $customerUserData->firstName = $registrationData->firstName;
        $customerUserData->lastName = $registrationData->lastName;
        $customerUserData->telephone = $registrationData->telephone;

        return $customerUserUpdateData;
    }
}
