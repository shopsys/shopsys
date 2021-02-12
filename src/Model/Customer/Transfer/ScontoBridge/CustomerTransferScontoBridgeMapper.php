<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\ScontoBridgeDistributionChannelResolver;
use App\Component\ScontoBridge\Transfer\ScontoBridgeTitleResolver;
use App\Model\Country\CountryFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use DateTime;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;

class CustomerTransferScontoBridgeMapper
{
    public const CUSTOMER_TYPE_INDIVIDUAL = 0;
    public const CUSTOMER_TYPE_COMPANY = 1;
    private const DEFAULT_TITLE = 1;

    /**
     * @var \App\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private CustomerUserUpdateDataFactory $customerUserUpdateDataFactory;

    /**
     * @var \App\Model\Country\CountryFacade
     */
    private CountryFacade $countryFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserDataFactory
     */
    private CustomerUserDataFactory $customerUserDataFactory;

    /**
     * @var ScontoBridgeDistributionChannelResolver
     */
    private ScontoBridgeDistributionChannelResolver $distributionChannelResolver;

    /**
     * @var ScontoBridgeTitleResolver
     */
    private ScontoBridgeTitleResolver $scontoBridgeTitleResolver;

    /**
     * @param CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param CountryFacade $countryFacade
     * @param CustomerUserDataFactory $customerUserDataFactory
     * @param ScontoBridgeDistributionChannelResolver $distributionChannelResolver
     * @param ScontoBridgeTitleResolver $scontoBridgeTitleResolver
     */
    public function __construct(
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        CountryFacade $countryFacade,
        CustomerUserDataFactory $customerUserDataFactory,
        ScontoBridgeDistributionChannelResolver $distributionChannelResolver,
        ScontoBridgeTitleResolver $scontoBridgeTitleResolver
    ) {
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->countryFacade = $countryFacade;
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->distributionChannelResolver = $distributionChannelResolver;
        $this->scontoBridgeTitleResolver = $scontoBridgeTitleResolver;
    }

    /**
     * @param array $scontoBridgeCustomerData
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function mapScontoBridgeCustomerDataToCustomerUserUpdateData(array $scontoBridgeCustomerData, ?CustomerUser $customerUser): CustomerUserUpdateData
    {
        if ($customerUser === null) {
            $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();

            /** @var \App\Model\Customer\User\CustomerUserData $customerUserData */
            $customerUserData = $this->customerUserDataFactory->createForDomainId(
                $this->distributionChannelResolver->getDomainIdByDistributionChannelCode(
                    $scontoBridgeCustomerData['distributionChannelCode']
                )
            );
            $customerUserData->erpCustomerNumber = $scontoBridgeCustomerData['erpCustomerNumber'];
            $customerUserData->createdAt = new DateTime($scontoBridgeCustomerData['modificationTime']);
        } else {
            $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
            /** @var \App\Model\Customer\User\CustomerUserData $customerUserData */
            $customerUserData = $customerUserUpdateData->customerUserData;

            if ($customerUserData->erpCustomerNumber === null) {
                $customerUserData->erpCustomerNumber = $scontoBridgeCustomerData['erpCustomerNumber'];
            }
        }

        /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $deliveryAddressData = $customerUserUpdateData->deliveryAddressData;

        $customerUserUpdateData->sendRegistrationMail = false;

        $customerUserData->email = $scontoBridgeCustomerData['email'];
        $customerUserData->newsletterSubscription = $scontoBridgeCustomerData['newsletter'];
        $customerUserData->telephone = $this->getPhoneNumberByScontoBridgeCustomerData($scontoBridgeCustomerData);

        $billingAddressData->street = $scontoBridgeCustomerData['primaryAddress']['street'];
        $billingAddressData->city = $scontoBridgeCustomerData['primaryAddress']['city'];
        $billingAddressData->postcode = $scontoBridgeCustomerData['primaryAddress']['zipCode'];
        $billingAddressData->country = $this->distributionChannelResolver->getCountryByDistributionChannelCode(
            $scontoBridgeCustomerData['distributionChannelCode']
        );

        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->street = $scontoBridgeCustomerData['primaryAddress']['street'];
        $deliveryAddressData->city = $scontoBridgeCustomerData['primaryAddress']['city'];
        $deliveryAddressData->postcode = $scontoBridgeCustomerData['primaryAddress']['zipCode'];
        $deliveryAddressData->country = $this->distributionChannelResolver->getCountryByDistributionChannelCode(
            $scontoBridgeCustomerData['distributionChannelCode']
        );
        $deliveryAddressData->telephone = $this->getPhoneNumberByScontoBridgeCustomerData($scontoBridgeCustomerData);

        if ($scontoBridgeCustomerData['customerType'] === self::CUSTOMER_TYPE_INDIVIDUAL) {
            $billingAddressData->companyCustomer = false;

            $customerUserData->gender = $this->scontoBridgeTitleResolver->getGenderByIndividualTitle(
                $scontoBridgeCustomerData['individual']['individualTitle']
            );
            $customerUserData->firstName = $scontoBridgeCustomerData['individual']['firstName'];
            $customerUserData->lastName = $scontoBridgeCustomerData['individual']['lastName'];

            $deliveryAddressData->firstName = $scontoBridgeCustomerData['individual']['firstName'];
            $deliveryAddressData->lastName = $scontoBridgeCustomerData['individual']['lastName'];
        } elseif ($scontoBridgeCustomerData['customerType'] === self::CUSTOMER_TYPE_COMPANY) {
            $billingAddressData->companyCustomer = true;

            $billingAddressData->companyName = $scontoBridgeCustomerData['company']['name'];
            $billingAddressData->companyNumber = $scontoBridgeCustomerData['company']['companyNumber'];
            $billingAddressData->companyTaxNumber = $scontoBridgeCustomerData['company']['taxNumber'];
            $billingAddressData->companyVatNumber = $scontoBridgeCustomerData['company']['vatNumber'];

            $deliveryAddressData->companyName = $scontoBridgeCustomerData['company']['name'];
        }

        $customerUserUpdateData->customerUserData = $customerUserData;

        return $customerUserUpdateData;
    }

    /**
     * @param array $scontoBridgeCustomerData
     * @return string|null
     */
    private function getPhoneNumberByScontoBridgeCustomerData(array $scontoBridgeCustomerData): ?string
    {
        if ($scontoBridgeCustomerData['phoneNumber'] === null) {
            return null;
        }

        return $scontoBridgeCustomerData['phoneNumber'];
    }
}
