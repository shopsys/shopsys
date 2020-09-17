<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\Domain\Domain;
use App\Component\ScontoBridge\Transfer\Exception\ScontoBridgeDistributionChannelResolverException;
use App\Component\ScontoBridge\Transfer\ScontoBridgeDistributionChannelResolver;
use App\Component\ScontoBridge\Transfer\ScontoBridgeTitleResolver;
use App\Model\Country\CountryDataInvalidException;
use App\Model\Country\CountryFacade;
use App\Model\Customer\BillingAddress;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser\ScontoBridgeCompany;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser\ScontoBridgeIndividual;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser\ScontoBridgePrimaryAddress;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use DateTime;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;

class CustomerTransferScontoBridgeMapper
{
    public const CUSTOMER_TYPE_INDIVIDUAL = 0;
    public const CUSTOMER_TYPE_COMPANY = 1;

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
            $billingAddressData->companyTaxNumber = $scontoBridgeCustomerData['company']['vatNumber'];
            $billingAddressData->companyNumberWithVat = $scontoBridgeCustomerData['company']['taxNumber'];

            $deliveryAddressData->companyName = $scontoBridgeCustomerData['company']['name'];
        }

        $customerUserUpdateData->customerUserData = $customerUserData;

        return $customerUserUpdateData;
    }

    /**
     * @param CustomerUser $customerUser
     * @return ScontoBridgeErpUser
     */
    public function mapCustomerUserToScontoBridgeCustomerData(CustomerUser $customerUser): ScontoBridgeErpUser
    {
        $erpUser = new ScontoBridgeErpUser();
        $erpUser->setEshopId($customerUser->getId());
        $erpUser->setEmail($customerUser->getEmail());
        $erpUser->setNewsletter($customerUser->isNewsletterSubscription());

        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        $country = $billingAddress->getCountry();
        if ($country === null) {
            throw new CustomerTransferScontoBridgeMapperException(
                sprintf('Country not defined for customer user id \'%d\' - billing address', $customerUser->getId())
            );
        }
        $erpUser->setDistributionChannelCode(
            $this->getDistributionChannelByCountry($country)
        );
        $erpUser->setPhonePrefix($this->getPhonePrefixByCountry($country));
        $erpUser->setPhoneNumber($customerUser->getTelephone());

        if ($customerUser->getDefaultDeliveryAddress() !== null) {
            $erpUser->setPrimaryAddress($this->mapPrimaryAddress($customerUser));
        }

        if ($billingAddress->isCompanyCustomer()) {
            $erpUser->setCompany($this->mapCompany($customerUser));
            $erpUser->setCustomerType(self::CUSTOMER_TYPE_COMPANY);
        } else {
            $erpUser->setIndividual($this->mapIndividual($customerUser));
            $erpUser->setCustomerType(self::CUSTOMER_TYPE_INDIVIDUAL);
        }

        return $erpUser;
    }

    /**
     * @param array $scontoBridgeCustomerData
     * @return string|null
     */
    private function getPhoneNumberByScontoBridgeCustomerData(array $scontoBridgeCustomerData): ?string
    {
        if ($scontoBridgeCustomerData['phoneNumber'] === null || $scontoBridgeCustomerData['phonePrefix'] === null) {
            return null;
        }

        return '+' . $scontoBridgeCustomerData['phonePrefix'] . $scontoBridgeCustomerData['phoneNumber'];
    }

    /**
     * @param CustomerUser $customer
     * @return ScontoBridgeCompany
     */
    private function mapCompany(CustomerUser $customer): ScontoBridgeCompany
    {
        /** @var BillingAddress $billingAddress */
        $billingAddress = $customer->getCustomer()->getBillingAddress();

        $erpCompany = new ScontoBridgeCompany();
        $erpCompany->setCompanyNumber($billingAddress->getCompanyNumber());
        $erpCompany->setName($billingAddress->getCompanyName());
        $erpCompany->setVatNumber($billingAddress->getCompanyTaxNumber());

        $companyNumberWithVat = $billingAddress->getCompanyNumberWithVat();
        if ($companyNumberWithVat !== null) {
            $erpCompany->setTaxNumber($companyNumberWithVat);
        }

        return $erpCompany;
    }

    /**
     * @param CustomerUser $customerUser
     * @return ScontoBridgePrimaryAddress|null
     */
    private function mapPrimaryAddress(CustomerUser $customerUser): ?ScontoBridgePrimaryAddress
    {
        $userDefaultAddress = $customerUser->getDefaultDeliveryAddress();
        if ($userDefaultAddress === null) {
            return null;
        }

        $erpAddress = new ScontoBridgePrimaryAddress();
        $erpAddress->setStreet($userDefaultAddress->getStreet());
        $erpAddress->setCity($userDefaultAddress->getCity());
        $country = $userDefaultAddress->getCountry();
        if ($country !== null) {
            $erpAddress->setCountry($country->getCode());
        }
        $erpAddress->setZipCode($userDefaultAddress->getPostcode());

        return $erpAddress;
    }

    /**
     * @param CustomerUser $customerUser
     * @return ScontoBridgeIndividual
     */
    private function mapIndividual(CustomerUser $customerUser): ScontoBridgeIndividual
    {
        $erpIndividual = new ScontoBridgeIndividual();

        $erpIndividual->setIndividualTitle(
            $this->scontoBridgeTitleResolver->getIndividualTitleByGender(
                $customerUser->getGender()
            )
        );
        $erpIndividual->setFirstName($customerUser->getFirstName());
        $erpIndividual->setLastName($customerUser->getLastName());

        return $erpIndividual;
    }

    /**
     * @param Country $country
     * @return int
     */
    private function getPhonePrefixByCountry(Country $country): int
    {
        $domainId = $this->getDomainIdByCountryCode($country->getCode());

        try {
            return $this->countryFacade->getPhonePrefixByDomainId($domainId);
        } catch (CountryDataInvalidException $e) {
            throw new CustomerTransferScontoBridgeMapperException($e->getMessage(), $e);
        }
    }

    /**
     * @param string $code
     * @return int
     */
    private function getDomainIdByCountryCode(string $code): int
    {
        try {
            return $this->countryFacade->getDomainIdByCountryCode($code);
        } catch (CountryDataInvalidException $e) {
            throw new CustomerTransferScontoBridgeMapperException($e->getMessage(), $e);
        }
    }

    /**
     * @param Country $country
     * @return int
     */
    private function getDistributionChannelByCountry(Country $country): int
    {
        try {
            return $this->distributionChannelResolver->getDistributionChannelCodeByCountry($country);
        } catch (ScontoBridgeDistributionChannelResolverException $e) {
            throw new CustomerTransferScontoBridgeMapperException($e->getMessage(), $e);
        }
    }
}
