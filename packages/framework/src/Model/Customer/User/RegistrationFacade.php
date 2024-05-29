<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class RegistrationFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationData $registrationData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function register(RegistrationData $registrationData): CustomerUser
    {
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

        $customerUser = $this->customerUserFacade->create($customerUserUpdateData);

        if ($customerUser->isNewsletterSubscription()) {
            $this->newsletterFacade->addSubscribedEmailIfNotExists($customerUser->getEmail(), $customerUser->getDomainId());
        }

        return $customerUser;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\RegistrationData $registrationData
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
