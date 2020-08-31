<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Model\Country\CountryFacade;
use App\Model\Customer\Transfer\ScontoBridge\Entity\ScontoBridgeErpUser;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;

class CustomerTransferScontoBridgeMapper
{
    public const CUSTOMER_TYPE_INDIVIDUAL = 0;
    public const CUSTOMER_TYPE_COMPANY = 1;
    public const INDIVIDUAL_TITLE_MALE = 0;
    public const INDIVIDUAL_TITLE_FEMALE = 1;
    public const DISTRIBUTION_CHANEL_CODE_CZ = 421;
    public const DISTRIBUTION_CHANEL_CODE_SK = 422;
    private const DISTRIBUTION_CODES_BY_DOMAIN = [
        Domain::FIRST_DOMAIN_ID => self::DISTRIBUTION_CHANEL_CODE_CZ,
        Domain::SECOND_DOMAIN_ID => self::DISTRIBUTION_CHANEL_CODE_SK
    ];

    /**
     * @var \App\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private $customerUserUpdateDataFactory;

    /**
     * @var \App\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserDataFactory
     */
    private $customerUserDataFactory;

    /**
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Country\CountryFacade $countryFacade
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     */
    public function __construct(
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        CountryFacade $countryFacade,
        CustomerUserDataFactory $customerUserDataFactory
    ) {
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->countryFacade = $countryFacade;
        $this->customerUserDataFactory = $customerUserDataFactory;
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
            $customerUserData = $this->customerUserDataFactory->createForDomainId($this->getDomainIdByDistributionChannelCode($scontoBridgeCustomerData['distributionChannelCode']));
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
        $billingAddressData->country = $this->getCountryByDistributionChannelCode($scontoBridgeCustomerData['distributionChannelCode']);

        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->street = $scontoBridgeCustomerData['primaryAddress']['street'];
        $deliveryAddressData->city = $scontoBridgeCustomerData['primaryAddress']['city'];
        $deliveryAddressData->postcode = $scontoBridgeCustomerData['primaryAddress']['zipCode'];
        $deliveryAddressData->country = $this->getCountryByDistributionChannelCode($scontoBridgeCustomerData['distributionChannelCode']);
        $deliveryAddressData->telephone = $this->getPhoneNumberByScontoBridgeCustomerData($scontoBridgeCustomerData);

        if ($scontoBridgeCustomerData['customerType'] === self::CUSTOMER_TYPE_INDIVIDUAL) {
            $billingAddressData->companyCustomer = false;

            $customerUserData->gender = $this->getGenderByIndividualTitle($scontoBridgeCustomerData['individual']['individualTitle']);
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

    public function mapCustomerUserToScontoBridgeCustomerData(CustomerUser $customerUser): ScontoBridgeErpUser
    {
        $erpUser = new ScontoBridgeErpUser();
        $erpUser->setEshopId($customerUser->getId());
        $erpUser->setEmail($customerUser->getEmail());
        $erpUser->setNewsletter($customerUser->isNewsletterSubscription());

        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        $erpUser->setDistributionChannelCode($this->getDistributionChannelCodeByCountry($billingAddress->getCountry()));
        $erpUser->setPhonePrefix(420); //fixme: not yet iplemented
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
     * @param int $individualTitle
     * @return string|null
     */
    private function getGenderByIndividualTitle(int $individualTitle): ?string
    {
        if ($individualTitle === self::INDIVIDUAL_TITLE_MALE) {
            return CustomerUser::GENDER_MALE;
        }

        if ($individualTitle === self::INDIVIDUAL_TITLE_FEMALE) {
            return CustomerUser::GENDER_FEMALE;
        }

        return null;
    }

    /**
     * @param int|null $distributionChannelCode
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    private function getCountryByDistributionChannelCode(?int $distributionChannelCode): ?Country
    {
        if ($distributionChannelCode === self::DISTRIBUTION_CHANEL_CODE_CZ) {
            return $this->countryFacade->findByCode(CountryFacade::COUNTRY_CODES_BY_DOMAIN_ID[Domain::FIRST_DOMAIN_ID]);
        }

        if ($distributionChannelCode === self::DISTRIBUTION_CHANEL_CODE_SK) {
            return $this->countryFacade->findByCode(CountryFacade::COUNTRY_CODES_BY_DOMAIN_ID[Domain::SECOND_DOMAIN_ID]);
        }

        return null;
    }

    private function getDistributionChannelCodeByCountry(Country $country): ?int
    {
        $domainIds = array_flip(CountryFacade::COUNTRY_CODES_BY_DOMAIN_ID);
        if (array_key_exists($country->getCode(), $domainIds) === false) {
            throw new \Exception('unknown country code'); //fixme
        }

        $domainId = $domainIds[$country->getCode()];
        if (array_key_exists($domainId, self::DISTRIBUTION_CODES_BY_DOMAIN) === false) {
            throw new \Exception('unknown domain'); //fixme
        }

        return self::DISTRIBUTION_CODES_BY_DOMAIN[$domainId];
    }

    /**
     * @param int|null $countryString
     * @return int|null
     */
    private function getDomainIdByDistributionChannelCode(?int $countryString): ?int
    {
        if ($countryString === self::DISTRIBUTION_CHANEL_CODE_CZ) {
            return Domain::FIRST_DOMAIN_ID;
        }

        if ($countryString === self::DISTRIBUTION_CHANEL_CODE_SK) {
            return Domain::SECOND_DOMAIN_ID;
        }

        return null;
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

    private function mapCompany(CustomerUser $customer): ScontoBridgeErpUser\ScontoBridgeCompany
    {
        $billingAddress = $customer->getCustomer()->getBillingAddress();

        $erpCompany = new ScontoBridgeErpUser\ScontoBridgeCompany();
        $erpCompany->setCompanyNumber($billingAddress->getCompanyNumber());
        $erpCompany->setName($billingAddress->getCompanyName());
        $erpCompany->setVatNumber($billingAddress->getCompanyTaxNumber());

//        fixme pro slovensko
//        if ($billingAddress->getCountry()->getCode() === 'SK') {
//            $erpCompany->setTaxNumber();
//        }

        return $erpCompany;
    }

    private function mapPrimaryAddress(CustomerUser $customerUser): ?ScontoBridgeErpUser\ScontoBridgePrimaryAddress
    {
        $userDefaultAddress = $customerUser->getDefaultDeliveryAddress();
        if ($userDefaultAddress === null) {
            return null;
        }

        $erpAddress = new ScontoBridgeErpUser\ScontoBridgePrimaryAddress();
        $erpAddress->setStreet($userDefaultAddress->getStreet());
        $erpAddress->setCity($userDefaultAddress->getCity());
        $country = $userDefaultAddress->getCountry();
        if ($country !== null) {
            $erpAddress->setCountry($country->getCode());
        }
        $erpAddress->setZipCode($userDefaultAddress->getPostcode());
        $erpAddress->setId($userDefaultAddress->getId()); //fixme

        return $erpAddress;
    }

    private function mapIndividual(CustomerUser $customerUser): ScontoBridgeErpUser\ScontoBridgeIndividual
    {
        $erpIndividual = new ScontoBridgeErpUser\ScontoBridgeIndividual();

        $erpIndividual->setIndividualTitle($customerUser->getGender());
        $erpIndividual->setFirstName($customerUser->getFirstName());
        $erpIndividual->setLastName($customerUser->getLastName());
        $erpIndividual->setBirthDate(''); //fixme not implemented

        return $erpIndividual;
    }
}
