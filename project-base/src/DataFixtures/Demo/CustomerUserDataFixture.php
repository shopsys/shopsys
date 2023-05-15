<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\User\CustomerUserDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;

class CustomerUserDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const USER_WITH_RESET_PASSWORD_HASH = 'user_with_reset_password_hash';

    private const KEY_CUSTOMER_USER_DATA = 'customerUserData';
    private const KEY_BILLING_ADDRESS = 'billingAddress';
    private const KEY_DELIVERY_ADDRESS = 'deliveryAddress';

    private const KEY_CUSTOMER_USER_DATA_FIRST_NAME = 'firstName';
    private const KEY_CUSTOMER_USER_DATA_LAST_NAME = 'lastName';
    private const KEY_CUSTOMER_USER_DATA_EMAIL = 'email';
    private const KEY_CUSTOMER_USER_DATA_PASSWORD = 'password';
    private const KEY_CUSTOMER_USER_DATA_TELEPHONE = 'telephone';

    private const KEY_ADDRESS_COMPANY_CUSTOMER = 'companyCustomer';
    private const KEY_ADDRESS_COMPANY_NAME = 'companyName';
    private const KEY_ADDRESS_COMPANY_NUMBER = 'companyNumber';
    private const KEY_ADDRESS_STREET = 'street';
    private const KEY_ADDRESS_CITY = 'city';
    private const KEY_ADDRESS_POSTCODE = 'postcode';
    private const KEY_ADDRESS_COUNTRY = 'country';
    private const KEY_ADDRESS_ADDRESS_FILLED = 'addressFilled';
    private const KEY_ADDRESS_TELEPHONE = 'telephone';
    private const KEY_ADDRESS_FIRST_NAME = 'firstName';
    private const KEY_ADDRESS_LAST_NAME = 'lastName';

    private EntityManagerDecorator $em;

    private CustomerUserDataFactory $customerUserDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     */
    public function __construct(
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly Generator $faker,
        EntityManagerInterface $em,
        private readonly HashGenerator $hashGenerator,
        private readonly Domain $domain,
        private readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CustomerUserDataFactoryInterface $customerUserDataFactory,
    ) {
        $this->em = $em;
        $this->customerUserDataFactory = $customerUserDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $customersDataProvider = $this->getDistinctCustomerUsersDataProvider();
            } else {
                $customersDataProvider = $this->getDefaultCustomerUsersDataProvider();
            }

            foreach ($customersDataProvider as $customerDataProvider) {
                $customerUserUpdateData = $this->getCustomerUserUpdateData($domainId, $customerDataProvider);
                $customerUserUpdateData->customerUserData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');

                /** @var \App\Model\Customer\User\CustomerUser $customerUser */
                $customerUser = $this->customerUserFacade->create($customerUserUpdateData);
                if ($customerUser->getId() !== 1) {
                    continue;
                }

                $this->resetPassword($customerUser);
                $this->addReference(self::USER_WITH_RESET_PASSWORD_HASH, $customerUser);
            }
        }
    }

    /**
     * @param int $domainId
     * @param array $data
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    private function getCustomerUserUpdateData(int $domainId, array $data): CustomerUserUpdateData
    {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserData = $this->customerUserDataFactory->createForDomainId($domainId);
        $customerUserData->firstName = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_FIRST_NAME] ?? null;
        $customerUserData->lastName = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_LAST_NAME] ?? null;
        $customerUserData->email = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_EMAIL] ?? null;
        $customerUserData->password = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_PASSWORD] ?? null;
        $customerUserData->telephone = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_TELEPHONE] ?? null;
        $customerUserData->customer = $customerUserUpdateData->customerUserData->customer;

        $this->setBillingAddressData($customerUserUpdateData, $data[self::KEY_BILLING_ADDRESS]);

        if (isset($data[self::KEY_DELIVERY_ADDRESS])) {
            $this->setDeliveryAddressData($customerUserUpdateData, $data[self::KEY_DELIVERY_ADDRESS]);
        }

        $customerUserUpdateData->customerUserData = $customerUserData;

        return $customerUserUpdateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param array $billingAddressInputData
     */
    private function setBillingAddressData(CustomerUserUpdateData $customerUserUpdateData, array $billingAddressInputData): void
    {
        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_CUSTOMER];
        $billingAddressData->companyName = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_NAME] ?? null;
        $billingAddressData->companyNumber = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_NUMBER] ?? null;
        $billingAddressData->city = $billingAddressInputData[self::KEY_ADDRESS_CITY] ?? null;
        $billingAddressData->street = $billingAddressInputData[self::KEY_ADDRESS_STREET] ?? null;
        $billingAddressData->postcode = $billingAddressInputData[self::KEY_ADDRESS_POSTCODE] ?? null;
        $billingAddressData->country = $billingAddressInputData[self::KEY_ADDRESS_COUNTRY];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param array $deliveryAddressInputData
     */
    private function setDeliveryAddressData(CustomerUserUpdateData $customerUserUpdateData, array $deliveryAddressInputData): void
    {
        $deliveryAddressData = $customerUserUpdateData->deliveryAddressData;
        $deliveryAddressData->addressFilled = $deliveryAddressInputData[self::KEY_ADDRESS_ADDRESS_FILLED] ?? false;
        $deliveryAddressData->companyName = $deliveryAddressInputData[self::KEY_ADDRESS_COMPANY_NAME] ?? null;
        $deliveryAddressData->firstName = $deliveryAddressInputData[self::KEY_ADDRESS_FIRST_NAME] ?? null;
        $deliveryAddressData->lastName = $deliveryAddressInputData[self::KEY_ADDRESS_LAST_NAME] ?? null;
        $deliveryAddressData->city = $deliveryAddressInputData[self::KEY_ADDRESS_CITY] ?? null;
        $deliveryAddressData->postcode = $deliveryAddressInputData[self::KEY_ADDRESS_POSTCODE] ?? null;
        $deliveryAddressData->street = $deliveryAddressInputData[self::KEY_ADDRESS_STREET] ?? null;
        $deliveryAddressData->telephone = $deliveryAddressInputData[self::KEY_ADDRESS_TELEPHONE] ?? null;
        $deliveryAddressData->country = $deliveryAddressInputData[self::KEY_ADDRESS_COUNTRY];
    }

    /**
     * @return array
     */
    private function getDefaultCustomerUsersDataProvider(): array
    {
        return [
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Jaromír',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Jágr',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000123',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Shopsys',
                    self::KEY_ADDRESS_COMPANY_NUMBER => '123456',
                    self::KEY_ADDRESS_STREET => 'Hlubinská 10',
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_POSTCODE => '70200',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Igor',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anpilogov',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.3@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.3',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Budišov nad Budišovkou',
                    self::KEY_ADDRESS_STREET => 'Berounská 668',
                    self::KEY_ADDRESS_POSTCODE => '74787',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Hana',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrejsová',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.5@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.5',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000201',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Brno',
                    self::KEY_ADDRESS_STREET => 'Špilberk 210/1',
                    self::KEY_ADDRESS_POSTCODE => '66224',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Alexandr',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Ton',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.9@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.9',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Bohumín',
                    self::KEY_ADDRESS_STREET => 'Na Strzi 3',
                    self::KEY_ADDRESS_POSTCODE => '69084',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Pavel',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Nedvěd',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.10@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.10',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Turín',
                    self::KEY_ADDRESS_STREET => 'Turínská 5',
                    self::KEY_ADDRESS_POSTCODE => '12345',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_CITY => 'Bahamy',
                    self::KEY_ADDRESS_POSTCODE => '99999',
                    self::KEY_ADDRESS_STREET => 'Bahamská 99',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Rostislav',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Vítek',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'vitek@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Shopsys',
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_STREET => 'Hlubinská 5',
                    self::KEY_ADDRESS_POSTCODE => '70200',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_ADDRESS_FILLED => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Rockpoint',
                    self::KEY_ADDRESS_FIRST_NAME => 'Eva',
                    self::KEY_ADDRESS_LAST_NAME => 'Wallicová',
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_POSTCODE => '70030',
                    self::KEY_ADDRESS_STREET => 'Rudná 15',
                    self::KEY_ADDRESS_TELEPHONE => '123456789',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Ľubomír',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Novák',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.11@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'test123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Bratislava',
                    self::KEY_ADDRESS_STREET => 'Pražská 3218/1',
                    self::KEY_ADDRESS_POSTCODE => '81104',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_ADDRESS_FILLED => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Rockpoint',
                    self::KEY_ADDRESS_CITY => 'Bratislava',
                    self::KEY_ADDRESS_POSTCODE => '10100',
                    self::KEY_ADDRESS_STREET => 'Ostravská 55/65A',
                    self::KEY_ADDRESS_TELEPHONE => '758686320',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getDistinctCustomerUsersDataProvider(): array
    {
        return [
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Jana',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anovčínová',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.2@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.2',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000202',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Aš',
                    self::KEY_ADDRESS_STREET => 'Mikulášská 3/5',
                    self::KEY_ADDRESS_POSTCODE => '35201',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Ida',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anpilogova',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.4@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.4',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000203',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Praha 3-Žižkov',
                    self::KEY_ADDRESS_STREET => 'Mahlerovy sady 1',
                    self::KEY_ADDRESS_POSTCODE => '13000',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Petr',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrig',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.6@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.6',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000204',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Jeseník',
                    self::KEY_ADDRESS_STREET => 'Pražská 3218/1',
                    self::KEY_ADDRESS_POSTCODE => '81104',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_ADDRESS_FILLED => true,
                    self::KEY_ADDRESS_CITY => 'Opava',
                    self::KEY_ADDRESS_POSTCODE => '74601',
                    self::KEY_ADDRESS_STREET => 'Komenského 419/10',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Silva',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrigová',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.7@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.7',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000205',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_STREET => 'Hlučínská 1170',
                    self::KEY_ADDRESS_POSTCODE => '70200',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Derick',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Ansah',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.8@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.8',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000206',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Opava',
                    self::KEY_ADDRESS_STREET => 'Ostrožná 35',
                    self::KEY_ADDRESS_POSTCODE => '74601',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Johny',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'English',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '603123456',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Shopsys',
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_STREET => 'Hlubinská 917/20',
                    self::KEY_ADDRESS_POSTCODE => '70200',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            CountryDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customer
     */
    private function resetPassword(CustomerUser $customer)
    {
        $resetPasswordHash = $this->hashGenerator->generateHash(
            CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH,
        );
        $customer->setResetPasswordHash($resetPasswordHash);
        $this->em->flush();
    }
}
