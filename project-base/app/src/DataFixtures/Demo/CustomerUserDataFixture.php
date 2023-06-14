<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\User\CustomerUser;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;

class CustomerUserDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const USER_WITH_RESET_PASSWORD_HASH = 'user_with_reset_password_hash';

    public const CUSTOMER_PREFIX = 'customer_';

    private const KEY_CUSTOMER_USER_DATA = 'customerUserData';
    private const KEY_BILLING_ADDRESS = 'billingAddress';
    private const KEY_DELIVERY_ADDRESS = 'deliveryAddress';

    private const KEY_CUSTOMER_USER_DATA_FIRST_NAME = 'firstName';
    private const KEY_CUSTOMER_USER_DATA_LAST_NAME = 'lastName';
    private const KEY_CUSTOMER_USER_DATA_EMAIL = 'email';
    private const KEY_CUSTOMER_USER_DATA_PASSWORD = 'password';
    private const KEY_CUSTOMER_USER_DATA_TELEPHONE = 'telephone';
    private const KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION = 'newsletterSubscription';
    private const KEY_CUSTOMER_USER_DATA_UUID = 'uuid';

    private const KEY_ADDRESS_COMPANY_CUSTOMER = 'companyCustomer';
    private const KEY_ADDRESS_COMPANY_NAME = 'companyName';
    private const KEY_ADDRESS_COMPANY_NUMBER = 'companyNumber';
    private const KEY_ADDRESS_COMPANY_TAX_NUMBER = 'companyTaxNumber';
    private const KEY_ADDRESS_STREET = 'street';
    private const KEY_ADDRESS_CITY = 'city';
    private const KEY_ADDRESS_POSTCODE = 'postcode';
    private const KEY_ADDRESS_COUNTRY = 'country';
    private const KEY_ADDRESS_ADDRESS_FILLED = 'addressFilled';
    private const KEY_ADDRESS_TELEPHONE = 'telephone';
    private const KEY_ADDRESS_FIRST_NAME = 'firstName';
    private const KEY_ADDRESS_LAST_NAME = 'lastName';
    private const KEY_ADDRESS_UUID = 'uuid';

    /**
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     */
    public function __construct(
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly Generator $faker,
        private readonly EntityManagerInterface $em,
        private readonly HashGenerator $hashGenerator,
        private readonly Domain $domain,
        private readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        private readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
    ) {
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

                $this->addReference(self::CUSTOMER_PREFIX . $customerUser->getId(), $customerUser);

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
     * @return \App\Model\Customer\User\CustomerUserUpdateData
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
        $customerUserData->newsletterSubscription = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION] ?? false;
        $customerUserData->uuid = $data[self::KEY_CUSTOMER_USER_DATA][self::KEY_CUSTOMER_USER_DATA_UUID];

        $this->setBillingAddressData($customerUserUpdateData, $data[self::KEY_BILLING_ADDRESS]);

        if (isset($data[self::KEY_DELIVERY_ADDRESS])) {
            $this->setDeliveryAddressData($customerUserUpdateData, $data[self::KEY_DELIVERY_ADDRESS]);
        }

        $customerUserUpdateData->customerUserData = $customerUserData;

        return $customerUserUpdateData;
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
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => '7b817d8b-41a3-4fc0-8570-08c9989f6dd9',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Shopsys',
                    self::KEY_ADDRESS_COMPANY_NUMBER => '12345678',
                    self::KEY_ADDRESS_COMPANY_TAX_NUMBER => 'CZ65432123',
                    self::KEY_ADDRESS_STREET => 'Hlubinská 10',
                    self::KEY_ADDRESS_CITY => 'Ostrava',
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
                    self::KEY_ADDRESS_STREET => 'Rudná 123',
                    self::KEY_ADDRESS_TELEPHONE => '123456789',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => '2339624f-10d4-43e6-80bd-6a8a4ef23186',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Igor',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anpilogov',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.3@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.3',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => '9b1099f9-6ea2-40c8-aba2-9f786a9f8081',
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
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'ee92df79-55fd-4f09-95e5-efd4b5284fa5',
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
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => '01f4a522-2eab-4719-b1fa-c098229e0f94',
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
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'e7a91811-a444-4825-a39f-4193e4d26a50',
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
                    self::KEY_CUSTOMER_USER_DATA_UUID => '87511613-d0db-4fa8-8b29-50188d6bfa36',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Rostislav',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Vítek',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'vitek@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => false,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'f34b2e26-c1af-432b-8390-12c272881944',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Shopsys',
                    self::KEY_ADDRESS_COMPANY_NUMBER => '12345678',
                    self::KEY_ADDRESS_COMPANY_TAX_NUMBER => 'CZ65432123',
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
                    self::KEY_ADDRESS_UUID => 'd5595a22-cb85-4c05-846e-8475f09229ef',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Ľubomír',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Novák',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.11@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'test123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '606060606',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'a36645b5-6a89-43d1-9010-5e350b1cefc1',
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
                    self::KEY_ADDRESS_UUID => 'b296e9bc-8446-41aa-a192-fb4c2b8dd666',
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
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => false,
                    self::KEY_CUSTOMER_USER_DATA_UUID => '9def746b-a639-4a26-a04e-1e289c73ead6',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000202',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Aš',
                    self::KEY_ADDRESS_STREET => 'Mikulášská 3/5',
                    self::KEY_ADDRESS_POSTCODE => '35201',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => '05fdae0f-8d43-4081-823c-cfa0e92d6281',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Ida',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anpilogova',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.4@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.4',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'e8a46d96-0031-4cf7-a70a-73cd29fd3eeb',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000203',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Praha 3-Žižkov',
                    self::KEY_ADDRESS_STREET => 'Mahlerovy sady 1',
                    self::KEY_ADDRESS_POSTCODE => '13000',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => '711ad188-a1c3-4739-9961-50ccaaed0371',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Petr',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrig',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.6@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.6',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'db98661c-dc33-41bc-993e-b457cd1cc662',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000204',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Jeseník',
                    self::KEY_ADDRESS_STREET => 'Pražská 3218/1',
                    self::KEY_ADDRESS_POSTCODE => '81104',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => 'b4e73e2a-70f8-4583-b10a-691c91c26d56',
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_ADDRESS_FILLED => true,
                    self::KEY_ADDRESS_CITY => 'Opava',
                    self::KEY_ADDRESS_POSTCODE => '74601',
                    self::KEY_ADDRESS_STREET => 'Komenského 419/10',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => '40736c88-3829-4d76-932c-91fd003d9d67',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Silva',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrigová',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.7@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.7',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => '3ff77ae2-69f2-4a16-b93f-952a91d1509e',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000205',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_STREET => 'Hlučínská 1170',
                    self::KEY_ADDRESS_POSTCODE => '70200',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => 'fdfb03a4-9bb6-4f40-acd4-03f3352d54e5',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Derick',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Ansah',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.8@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.8',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => '5b252118-de72-41be-9716-0bc5a7fa29b8',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '605000206',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Opava',
                    self::KEY_ADDRESS_STREET => 'Ostrožná 35',
                    self::KEY_ADDRESS_POSTCODE => '74601',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                    self::KEY_ADDRESS_UUID => 'b57fa361-5121-4594-a838-6469aa61890e',
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Johny',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'English',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'user123',
                    self::KEY_CUSTOMER_USER_DATA_TELEPHONE => '603123456',
                    self::KEY_CUSTOMER_USER_DATA_NEWSLETTER_SUBSCRIPTION => true,
                    self::KEY_CUSTOMER_USER_DATA_UUID => 'd4304c47-64db-402a-ae70-a79d174f3911',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NUMBER => '12345678',
                    self::KEY_ADDRESS_COMPANY_TAX_NUMBER => 'CZ65432123',
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

    /**
     * @param \App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param array $billingAddressInputData
     */
    private function setBillingAddressData(
        CustomerUserUpdateData $customerUserUpdateData,
        array $billingAddressInputData,
    ): void {
        /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_CUSTOMER];
        $billingAddressData->companyName = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_NAME] ?? null;
        $billingAddressData->companyNumber = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_NUMBER] ?? null;
        $billingAddressData->companyTaxNumber = $billingAddressInputData[self::KEY_ADDRESS_COMPANY_TAX_NUMBER] ?? null;
        $billingAddressData->city = $billingAddressInputData[self::KEY_ADDRESS_CITY] ?? null;
        $billingAddressData->street = $billingAddressInputData[self::KEY_ADDRESS_STREET] ?? null;
        $billingAddressData->postcode = $billingAddressInputData[self::KEY_ADDRESS_POSTCODE] ?? null;
        $billingAddressData->country = $billingAddressInputData[self::KEY_ADDRESS_COUNTRY];
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserUpdateData $customerUserUpdateData
     * @param array $deliveryAddressInputData
     */
    private function setDeliveryAddressData(
        CustomerUserUpdateData $customerUserUpdateData,
        array $deliveryAddressInputData,
    ): void {
        /** @var \App\Model\Customer\DeliveryAddressData $deliveryAddressData */
        $deliveryAddressData = $customerUserUpdateData->deliveryAddressData;
        $deliveryAddressData->addressFilled = $deliveryAddressInputData[self::KEY_ADDRESS_ADDRESS_FILLED] ?? null;
        $deliveryAddressData->companyName = $deliveryAddressInputData[self::KEY_ADDRESS_COMPANY_NAME] ?? null;
        $deliveryAddressData->firstName = $deliveryAddressInputData[self::KEY_ADDRESS_FIRST_NAME] ?? null;
        $deliveryAddressData->lastName = $deliveryAddressInputData[self::KEY_ADDRESS_LAST_NAME] ?? null;
        $deliveryAddressData->city = $deliveryAddressInputData[self::KEY_ADDRESS_CITY] ?? null;
        $deliveryAddressData->postcode = $deliveryAddressInputData[self::KEY_ADDRESS_POSTCODE] ?? null;
        $deliveryAddressData->street = $deliveryAddressInputData[self::KEY_ADDRESS_STREET] ?? null;
        $deliveryAddressData->telephone = $deliveryAddressInputData[self::KEY_ADDRESS_TELEPHONE] ?? null;
        $deliveryAddressData->country = $deliveryAddressInputData[self::KEY_ADDRESS_COUNTRY];
        $deliveryAddressData->uuid = $deliveryAddressInputData[self::KEY_CUSTOMER_USER_DATA_UUID];
    }
}
