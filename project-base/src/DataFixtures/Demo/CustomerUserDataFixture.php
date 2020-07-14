<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    private $hashGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     */
    private $customerUserUpdateDataFactory;

    /**
     * @var \App\Model\Customer\User\CustomerUserDataFactory
     */
    private $customerUserDataFactory;

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
        CustomerUserFacade $customerUserFacade,
        Generator $faker,
        EntityManagerInterface $em,
        HashGenerator $hashGenerator,
        Domain $domain,
        CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        CustomerUserDataFactoryInterface $customerUserDataFactory
    ) {
        $this->customerUserFacade = $customerUserFacade;
        $this->faker = $faker;
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
        $this->domain = $domain;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
        $this->customerUserDataFactory = $customerUserDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
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
                if ($customerUser->getId() === 1) {
                    $this->resetPassword($customerUser);
                    $this->addReference(self::USER_WITH_RESET_PASSWORD_HASH, $customerUser);
                }
            }
        }
    }

    /**
     * @param int $domainId
     * @param array $data
     *
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

        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_COMPANY_CUSTOMER];
        $billingAddressData->companyName = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_COMPANY_NAME] ?? null;
        $billingAddressData->companyNumber = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_COMPANY_NUMBER] ?? null;
        $billingAddressData->city = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_CITY] ?? null;
        $billingAddressData->street = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_STREET] ?? null;
        $billingAddressData->postcode = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_POSTCODE] ?? null;
        $billingAddressData->country = $data[self::KEY_BILLING_ADDRESS][self::KEY_ADDRESS_COUNTRY];

        if (isset($data[self::KEY_DELIVERY_ADDRESS])) {
            $deliveryAddressData = $customerUserUpdateData->deliveryAddressData;
            $deliveryAddressData->addressFilled = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_ADDRESS_FILLED] ?? null;
            $deliveryAddressData->companyName = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_COMPANY_NAME] ?? null;
            $deliveryAddressData->firstName = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_FIRST_NAME] ?? null;
            $deliveryAddressData->lastName = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_LAST_NAME] ?? null;
            $deliveryAddressData->city = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_CITY] ?? null;
            $deliveryAddressData->postcode = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_POSTCODE] ?? null;
            $deliveryAddressData->street = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_STREET] ?? null;
            $deliveryAddressData->telephone = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_TELEPHONE] ?? null;
            $deliveryAddressData->country = $data[self::KEY_DELIVERY_ADDRESS][self::KEY_ADDRESS_COUNTRY];
        }

        $customerUserUpdateData->customerUserData = $customerUserData;
        $customerUserUpdateData->billingAddressData = $billingAddressData;

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
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => true,
                    self::KEY_ADDRESS_COMPANY_NAME => 'Shopsys',
                    self::KEY_ADDRESS_COMPANY_NUMBER => '123456',
                    self::KEY_ADDRESS_STREET => 'Hlubinská',
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
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Hana',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrejsová',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.5@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.5',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Brno',
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
                    self::KEY_ADDRESS_STREET => 'Rudná',
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
                    self::KEY_ADDRESS_STREET => 'Brněnská',
                    self::KEY_ADDRESS_POSTCODE => '1010',
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
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Aš',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Ida',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anpilogova',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.4@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.4',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Praha',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Petr',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrig',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.6@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.6',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Jeseník',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
                self::KEY_DELIVERY_ADDRESS => [
                    self::KEY_ADDRESS_ADDRESS_FILLED => true,
                    self::KEY_ADDRESS_CITY => 'Opava',
                    self::KEY_ADDRESS_POSTCODE => '70000',
                    self::KEY_ADDRESS_STREET => 'Ostravská',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Silva',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Anrigová',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.7@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.7',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Ostrava',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
            [
                self::KEY_CUSTOMER_USER_DATA => [
                    self::KEY_CUSTOMER_USER_DATA_FIRST_NAME => 'Derick',
                    self::KEY_CUSTOMER_USER_DATA_LAST_NAME => 'Ansah',
                    self::KEY_CUSTOMER_USER_DATA_EMAIL => 'no-reply.8@shopsys.com',
                    self::KEY_CUSTOMER_USER_DATA_PASSWORD => 'no-reply.8',
                ],
                self::KEY_BILLING_ADDRESS => [
                    self::KEY_ADDRESS_COMPANY_CUSTOMER => false,
                    self::KEY_ADDRESS_CITY => 'Opava',
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
                    self::KEY_ADDRESS_STREET => 'Hlubinská',
                    self::KEY_ADDRESS_POSTCODE => '70200',
                    self::KEY_ADDRESS_COUNTRY => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
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
        $resetPasswordHash = $this->hashGenerator->generateHash(CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
        $customer->setResetPasswordHash($resetPasswordHash);
        $this->em->flush($customer);
    }
}
