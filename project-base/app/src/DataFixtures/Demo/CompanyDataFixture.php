<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\BillingAddressDataFactory;
use App\Model\Customer\DeliveryAddressDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class CompanyDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE_DELIVERY_ADDRESS = '64401db9-a0d6-4aa1-a675-b6e4c6d9b554';
    private const string UUID_NAMESPACE_CUSTOMER = '0e331caa-43a5-11ef-95db-325096b39f47';

    public const string SHOPSYS_COMPANY = 'shopsys_company';
    public const string B2B_COMPANY_OWNER_EMAIL = 'jozef.novotny@shopsys.com';
    public const string B2B_COMPANY_SELF_MANAGE_USER_EMAIL = 'marek.horvat@shopsys.com';
    public const string B2B_COMPANY_LIMITED_USER_EMAIL = 'peter.kovac@shopsys.com';

    private const string KEY_CUSTOMER_USER_DATA = 'customerUserData';
    private const string KEY_DELIVERY_ADDRESS = 'deliveryAddress';

    private const string KEY_CUSTOMER_USER_DATA_FIRST_NAME = 'firstName';
    private const string KEY_CUSTOMER_USER_DATA_LAST_NAME = 'lastName';
    private const string KEY_CUSTOMER_USER_DATA_EMAIL = 'email';
    private const string KEY_CUSTOMER_USER_DATA_PASSWORD = 'password';
    private const string KEY_CUSTOMER_USER_DATA_TELEPHONE = 'telephone';

    private const string KEY_ADDRESS_STREET = 'street';
    private const string KEY_ADDRESS_CITY = 'city';
    private const string KEY_ADDRESS_POSTCODE = 'postcode';
    private const string KEY_ADDRESS_COUNTRY = 'country';
    private const string KEY_ADDRESS_TELEPHONE = 'telephone';
    private const string KEY_ADDRESS_FIRST_NAME = 'firstName';
    private const string KEY_ADDRESS_LAST_NAME = 'lastName';
    private const string KEY_CUSTOMER_USER_REFERENCE = 'customerUserReference';
    private const string KEY_CUSTOMER_ROLE_GROUP = 'roleGroup';

    /**
     * @param \Faker\Generator $faker
     * @param \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     */
    public function __construct(
        private readonly Generator $faker,
        private readonly BillingAddressDataFactory $billingAddressDataFactory,
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        private readonly DeliveryAddressFacade $deliveryAddressFacade,
        private readonly CustomerUserDataFactory $customerUserDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if (!$domainConfig->isB2b()) {
                continue;
            }

            $domainId = $domainConfig->getId();
            $customer = $this->createCustomerWithBillingAddress($domainId);
            $this->createCustomerUsers($customer);
            $this->addReferenceForDomain(self::SHOPSYS_COMPANY, $customer, $domainId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CountryDataFixture::class,
            PricingGroupDataFixture::class,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     */
    private function createCustomerUsers(Customer $customer): void
    {
        $customersDataProvider = $this->getDefaultCustomerUsersDataProvider();

        foreach ($customersDataProvider as $customerDataProvider) {
            $deliveryAddress = $this->createDeliveryAddress($customer, $customerDataProvider[self::KEY_DELIVERY_ADDRESS]);
            $customerUserdata = $this->createCustomerUserData($customerDataProvider[self::KEY_CUSTOMER_USER_DATA], $customer->getDomainId(), $deliveryAddress);
            $customerUser = $this->customerUserFacade->createCustomerUserWithActivationMail($customer, $customerUserdata);

            $customerUserReference = $customerDataProvider[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_REFERENCE];
            $this->addReferenceForDomain($customerUserReference, $customerUser, $customer->getDomainId());
        }
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    private function createCustomerWithBillingAddress(int $domainId): Customer
    {
        $billingAddressData = $this->billingAddressDataFactory->create();
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys Slovakia';
        $billingAddressData->companyNumber = '44633599';
        $billingAddressData->companyTaxNumber = '2022779044';
        $billingAddressData->street = 'Kráľov Brod 377';
        $billingAddressData->city = 'Kráľov Brod';
        $billingAddressData->postcode = '92541';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);

        return $this->customerUserFacade->createCustomerWithBillingAddress($domainId, $billingAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param array $deliveryAddressArrayData
     * @return \App\Model\Customer\DeliveryAddress
     */
    private function createDeliveryAddress(Customer $customer, array $deliveryAddressArrayData): DeliveryAddress
    {
        $deliveryAddressData = $this->deliveryAddressDataFactory->create();
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->customer = $customer;
        $deliveryAddressData->firstName = $deliveryAddressArrayData[self::KEY_ADDRESS_FIRST_NAME];
        $deliveryAddressData->lastName = $deliveryAddressArrayData[self::KEY_ADDRESS_LAST_NAME];
        $deliveryAddressData->street = $deliveryAddressArrayData[self::KEY_ADDRESS_STREET];
        $deliveryAddressData->city = $deliveryAddressArrayData[self::KEY_ADDRESS_CITY];
        $deliveryAddressData->postcode = $deliveryAddressArrayData[self::KEY_ADDRESS_POSTCODE];
        $deliveryAddressData->country = $deliveryAddressArrayData[self::KEY_ADDRESS_COUNTRY];
        $deliveryAddressData->telephone = $deliveryAddressArrayData[self::KEY_ADDRESS_TELEPHONE];
        $deliveryAddressData->uuid = Uuid::uuid5(
            self::UUID_NAMESPACE_DELIVERY_ADDRESS,
            $customer->getDomainId() . $deliveryAddressArrayData[self::KEY_ADDRESS_FIRST_NAME],
        )->toString();

        return $this->deliveryAddressFacade->createIfAddressFilled($deliveryAddressData);
    }

    /**
     * @param array $customerDataProvider
     * @param int $domainId
     * @param \App\Model\Customer\DeliveryAddress $defaultDeliveryAddress
     * @return \App\Model\Customer\User\CustomerUserData
     */
    private function createCustomerUserData(
        array $customerDataProvider,
        int $domainId,
        DeliveryAddress $defaultDeliveryAddress,
    ): CustomerUserData {
        $customerUserData = $this->customerUserDataFactory->create();
        $customerUserData->firstName = $customerDataProvider[self::KEY_CUSTOMER_USER_DATA_FIRST_NAME];
        $customerUserData->lastName = $customerDataProvider[self::KEY_CUSTOMER_USER_DATA_LAST_NAME];
        $customerUserData->email = $customerDataProvider[self::KEY_CUSTOMER_USER_DATA_EMAIL];
        $customerUserData->password = $customerDataProvider[self::KEY_CUSTOMER_USER_DATA_PASSWORD];
        $customerUserData->telephone = $customerDataProvider[self::KEY_CUSTOMER_USER_DATA_TELEPHONE];
        $customerUserData->uuid = Uuid::uuid5(
            self::UUID_NAMESPACE_CUSTOMER,
            $domainId . $customerDataProvider[self::KEY_CUSTOMER_USER_DATA_FIRST_NAME],
        )->toString();
        $customerUserData->createdAt = $this->faker->dateTimeBetween('-1 week');
        $customerUserData->defaultDeliveryAddress = $defaultDeliveryAddress;
        $customerUserData->pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, $domainId, PricingGroup::class);
        $customerUserData->domainId = $domainId;
        $customerUserData->roleGroup = $customerDataProvider[self::KEY_CUSTOMER_ROLE_GROUP];

        return $customerUserData;
    }

    /**
     * @return array
     */
    private function getDefaultCustomerUsersDataProvider(): array
    {
        return [
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Jozef',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Novotný',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => self::B2B_COMPANY_OWNER_EMAIL,
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060605',
                    self::KEY_CUSTOMER_USER_REFERENCE => self::B2B_COMPANY_OWNER_EMAIL,
                    self::KEY_CUSTOMER_ROLE_GROUP => $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER, CustomerUserRoleGroup::class),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_FIRST_NAME => 'Jozef',
                    self::KEY_ADDRESS_LAST_NAME => 'Novotný',
                    self::KEY_ADDRESS_CITY => 'Bratislava',
                    self::KEY_ADDRESS_POSTCODE => '83104',
                    self::KEY_ADDRESS_STREET => 'Račianska 157',
                    self::KEY_ADDRESS_TELEPHONE => '123456789',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Peter',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Kováč',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => self::B2B_COMPANY_LIMITED_USER_EMAIL,
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                    self::KEY_CUSTOMER_USER_REFERENCE => self::B2B_COMPANY_LIMITED_USER_EMAIL,
                    self::KEY_CUSTOMER_ROLE_GROUP => $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER, CustomerUserRoleGroup::class),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_FIRST_NAME => 'Eva',
                    self::KEY_ADDRESS_LAST_NAME => 'Svobodová',
                    self::KEY_ADDRESS_CITY => 'Košice',
                    self::KEY_ADDRESS_POSTCODE => '04001',
                    self::KEY_ADDRESS_STREET => 'Hlavná 20',
                    self::KEY_ADDRESS_TELEPHONE => '123456789',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Marek',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Horváth',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => self::B2B_COMPANY_SELF_MANAGE_USER_EMAIL,
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060607',
                    self::KEY_CUSTOMER_USER_REFERENCE => self::B2B_COMPANY_SELF_MANAGE_USER_EMAIL,
                    self::KEY_CUSTOMER_ROLE_GROUP => $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER, CustomerUserRoleGroup::class),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_FIRST_NAME => 'Marek',
                    self::KEY_ADDRESS_LAST_NAME => 'Horváth',
                    self::KEY_ADDRESS_CITY => 'Prešov',
                    self::KEY_ADDRESS_POSTCODE => '08001',
                    self::KEY_ADDRESS_STREET => 'Hlavná 55/65A',
                    self::KEY_ADDRESS_TELEPHONE => '758686320',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA, Country::class),
                ],
            ],
        ];
    }
}
