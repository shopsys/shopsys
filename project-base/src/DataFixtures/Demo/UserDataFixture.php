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
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface;

class UserDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const USER_WITH_RESET_PASSWORD_HASH = 'user_with_reset_password_hash';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\String\HashGenerator
     */
    protected $hashGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface
     */
    protected $customerUserDataFactory;

    /**
     * @var \App\Model\Customer\UserDataFactory
     */
    protected $userDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface
     */
    protected $billingAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface
     */
    protected $deliveryAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface
     */
    protected $customerFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade $customerUserFacade
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface $customerUserDataFactory
     * @param \App\Model\Customer\UserDataFactory $userDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface $customerFactory
     */
    public function __construct(
        CustomerUserFacade $customerUserFacade,
        Generator $faker,
        EntityManagerInterface $em,
        HashGenerator $hashGenerator,
        Domain $domain,
        CustomerUserDataFactoryInterface $customerUserDataFactory,
        UserDataFactoryInterface $userDataFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory,
        CustomerFactoryInterface $customerFactory
    ) {
        $this->customerUserFacade = $customerUserFacade;
        $this->faker = $faker;
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
        $this->domain = $domain;
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->userDataFactory = $userDataFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->deliveryAddressDataFactory = $deliveryAddressDataFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $customerUserData = $this->getDistinctCustomersData($domainId);
            } else {
                $customerUserData = $this->getDefaultCustomersData($domainId);
            }

            foreach ($customerUserData as $customerData) {
                $customerData->userData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');

                $customer = $this->customerUserFacade->create($customerData);
                if ($customer->getId() === 1) {
                    $this->resetPassword($customer);
                    $this->addReference(self::USER_WITH_RESET_PASSWORD_HASH, $customer);
                }
            }
        }
    }

    /**
     * @param int $domainId
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData[]
     */
    protected function getDefaultCustomersData(int $domainId): array
    {
        $customersUserData = [];

        // no-reply@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Jaromír';
        $userData->lastName = 'Jágr';
        $userData->email = 'no-reply@shopsys.com';
        $userData->password = 'user123';
        $userData->telephone = '605000123';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->companyNumber = '123456';
        $billingAddressData->street = 'Hlubinská';
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->postcode = '70200';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        // no-reply.3@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Igor';
        $userData->lastName = 'Anpilogov';
        $userData->email = 'no-reply.3@shopsys.com';
        $userData->password = 'no-reply.3';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Budišov nad Budišovkou';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        // no-reply.5@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Hana';
        $userData->lastName = 'Anrejsová';
        $userData->email = 'no-reply.5@shopsys.com';
        $userData->password = 'no-reply.5';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Brno';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        // no-reply.9@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Alexandr';
        $userData->lastName = 'Ton';
        $userData->email = 'no-reply.9@shopsys.com';
        $userData->password = 'no-reply.9';
        $userData->telephone = '606060606';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Bohumín';
        $billingAddressData->street = 'Na Strzi 3';
        $billingAddressData->postcode = '69084';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        // no-reply.10@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Pavel';
        $userData->lastName = 'Nedvěd';
        $userData->email = 'no-reply.10@shopsys.com';
        $userData->password = 'no-reply.10';
        $userData->telephone = '606060606';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Turín';
        $billingAddressData->street = 'Turínská 5';
        $billingAddressData->postcode = '12345';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $deliveryAddressData = $customerUserData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->city = 'Bahamy';
        $deliveryAddressData->postcode = '99999';
        $deliveryAddressData->street = 'Bahamská 99';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerUserData->deliveryAddressData = $deliveryAddressData;
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        // vitek@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Rostislav';
        $userData->lastName = 'Vítek';
        $userData->email = 'vitek@shopsys.com';
        $userData->password = 'user123';
        $userData->telephone = '606060606';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->street = 'Hlubinská 5';
        $billingAddressData->postcode = '70200';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $deliveryAddressData = $customerUserData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->companyName = 'Rockpoint';
        $deliveryAddressData->firstName = 'Eva';
        $deliveryAddressData->lastName = 'Wallicová';
        $deliveryAddressData->city = 'Ostrava';
        $deliveryAddressData->postcode = '70030';
        $deliveryAddressData->street = 'Rudná';
        $deliveryAddressData->telephone = '123456789';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerUserData->deliveryAddressData = $deliveryAddressData;
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        // no-reply.11@shopsys.com
        $customerUserData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Ľubomír';
        $userData->lastName = 'Novák';
        $userData->email = 'no-reply.11@shopsys.com';
        $userData->password = 'test123';
        $userData->telephone = '606060606';
        $userData->customer = $customerUserData->userData->customer;
        $billingAddressData = $customerUserData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Bratislava';
        $billingAddressData->street = 'Brněnská';
        $billingAddressData->postcode = '1010';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $deliveryAddressData = $customerUserData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->city = 'Bratislava';
        $deliveryAddressData->postcode = '10100';
        $deliveryAddressData->street = 'Ostravská 55/65A';
        $deliveryAddressData->telephone = '758686320';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $customerUserData->userData = $userData;
        $customerUserData->billingAddressData = $billingAddressData;
        $customersUserData[] = $customerUserData;

        return $customersUserData;
    }

    /**
     * @param int $domainId
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData[]
     */
    protected function getDistinctCustomersData(int $domainId): array
    {
        $customersData = [];

        // no-reply.2@shopsys.com
        $customerData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Jana';
        $userData->lastName = 'Anovčínová';
        $userData->email = 'no-reply.2@shopsys.com';
        $userData->password = 'no-reply.2';
        $userData->customer = $customerData->userData->customer;
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Aš';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.4@shopsys.com
        $customerData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Ida';
        $userData->lastName = 'Anpilogova';
        $userData->email = 'no-reply.4@shopsys.com';
        $userData->password = 'no-reply.4';
        $userData->customer = $customerData->userData->customer;
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Praha';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.6@shopsys.com
        $customerData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Petr';
        $userData->lastName = 'Anrig';
        $userData->email = 'no-reply.6@shopsys.com';
        $userData->password = 'no-reply.6';
        $userData->customer = $customerData->userData->customer;
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Jeseník';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $deliveryAddressData = $customerData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->city = 'Opava';
        $deliveryAddressData->postcode = '70000';
        $deliveryAddressData->street = 'Ostravská';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.7@shopsys.com
        $customerData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Silva';
        $userData->lastName = 'Anrigová';
        $userData->email = 'no-reply.7@shopsys.com';
        $userData->password = 'no-reply.7';
        $userData->customer = $customerData->userData->customer;
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.8@shopsys.com
        $customerData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Derick';
        $userData->lastName = 'Ansah';
        $userData->email = 'no-reply.8@shopsys.com';
        $userData->password = 'no-reply.8';
        $userData->customer = $customerData->userData->customer;
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Opava';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply@shopsys.com
        $customerData = $this->customerUserDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Johny';
        $userData->lastName = 'English';
        $userData->email = 'no-reply@shopsys.com';
        $userData->password = 'user123';
        $userData->telephone = '603123456';
        $userData->customer = $customerData->userData->customer;
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->street = 'Hlubinská';
        $billingAddressData->postcode = '70200';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        return $customersData;
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
     * @param \App\Model\Customer\User $customer
     */
    protected function resetPassword(User $customer)
    {
        $resetPasswordHash = $this->hashGenerator->generateHash(CustomerUserPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
        $customer->setResetPasswordHash($resetPasswordHash);
        $this->em->flush($customer);
    }
}
