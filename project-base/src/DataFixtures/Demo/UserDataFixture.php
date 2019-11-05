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
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\CustomerPasswordFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface;

class UserDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const USER_WITH_RESET_PASSWORD_HASH = 'user_with_reset_password_hash';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     */
    protected $customerFacade;

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
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface
     */
    protected $customerDataFactory;

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
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \App\Model\Customer\UserDataFactory $userDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
     */
    public function __construct(
        CustomerFacade $customerFacade,
        Generator $faker,
        EntityManagerInterface $em,
        HashGenerator $hashGenerator,
        Domain $domain,
        CustomerDataFactoryInterface $customerDataFactory,
        UserDataFactoryInterface $userDataFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
    ) {
        $this->customerFacade = $customerFacade;
        $this->faker = $faker;
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
        $this->domain = $domain;
        $this->customerDataFactory = $customerDataFactory;
        $this->userDataFactory = $userDataFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->deliveryAddressDataFactory = $deliveryAddressDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $customersData = $this->getDistinctCustomersData($domainId);
            } else {
                $customersData = $this->getDefaultCustomersData($domainId);
            }

            foreach ($customersData as $customerData) {
                $customerData->userData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');

                $customer = $this->customerFacade->create($customerData);
                if ($customer->getId() === 1) {
                    $this->resetPassword($customer);
                    $this->addReference(self::USER_WITH_RESET_PASSWORD_HASH, $customer);
                }
            }
        }
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData[]
     */
    protected function getDefaultCustomersData(int $domainId): array
    {
        $customersData = [];

        // no-reply@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = 'Jaromír';
        $userData->lastName = 'Jágr';
        $userData->email = 'no-reply@shopsys.com';
        $userData->password = 'user123';
        $userData->telephone = '605000123';
        $billingAddressData = $customerData->billingAddressData;
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->companyNumber = '123456';
        $billingAddressData->street = 'Hlubinská';
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->postcode = '70200';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.3@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Igor';
        $userData->lastName = 'Anpilogov';
        $userData->email = 'no-reply.3@shopsys.com';
        $userData->password = 'no-reply.3';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Budišov nad Budišovkou';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.5@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Hana';
        $userData->lastName = 'Anrejsová';
        $userData->email = 'no-reply.5@shopsys.com';
        $userData->password = 'no-reply.5';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Brno';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.9@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Alexandr';
        $userData->lastName = 'Ton';
        $userData->email = 'no-reply.9@shopsys.com';
        $userData->password = 'no-reply.9';
        $userData->telephone = '606060606';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Bohumín';
        $billingAddressData->street = 'Na Strzi 3';
        $billingAddressData->postcode = '69084';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.10@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Pavel';
        $userData->lastName = 'Nedvěd';
        $userData->email = 'no-reply.10@shopsys.com';
        $userData->password = 'no-reply.10';
        $userData->telephone = '606060606';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Turín';
        $billingAddressData->street = 'Turínská 5';
        $billingAddressData->postcode = '12345';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $deliveryAddressData = $customerData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->city = 'Bahamy';
        $deliveryAddressData->postcode = '99999';
        $deliveryAddressData->street = 'Bahamská 99';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->deliveryAddressData = $deliveryAddressData;
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // vitek@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Rostislav';
        $userData->lastName = 'Vítek';
        $userData->email = 'vitek@shopsys.com';
        $userData->password = 'user123';
        $userData->telephone = '606060606';
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->street = 'Hlubinská 5';
        $billingAddressData->postcode = '70200';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $deliveryAddressData = $customerData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->companyName = 'Rockpoint';
        $deliveryAddressData->firstName = 'Eva';
        $deliveryAddressData->lastName = 'Wallicová';
        $deliveryAddressData->city = 'Ostrava';
        $deliveryAddressData->postcode = '70030';
        $deliveryAddressData->street = 'Rudná';
        $deliveryAddressData->telephone = '123456789';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->deliveryAddressData = $deliveryAddressData;
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.11@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Ľubomír';
        $userData->lastName = 'Novák';
        $userData->email = 'no-reply.11@shopsys.com';
        $userData->password = 'test123';
        $userData->telephone = '606060606';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Bratislava';
        $billingAddressData->street = 'Brněnská';
        $billingAddressData->postcode = '1010';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $deliveryAddressData = $customerData->deliveryAddressData;
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->city = 'Bratislava';
        $deliveryAddressData->postcode = '10100';
        $deliveryAddressData->street = 'Ostravská 55/65A';
        $deliveryAddressData->telephone = '758686320';
        $deliveryAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        return $customersData;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData[]
     */
    protected function getDistinctCustomersData(int $domainId): array
    {
        $customersData = [];

        // no-reply.2@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Jana';
        $userData->lastName = 'Anovčínová';
        $userData->email = 'no-reply.2@shopsys.com';
        $userData->password = 'no-reply.2';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Aš';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.4@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Ida';
        $userData->lastName = 'Anpilogova';
        $userData->email = 'no-reply.4@shopsys.com';
        $userData->password = 'no-reply.4';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Praha';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.6@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Petr';
        $userData->lastName = 'Anrig';
        $userData->email = 'no-reply.6@shopsys.com';
        $userData->password = 'no-reply.6';
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
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Silva';
        $userData->lastName = 'Anrigová';
        $userData->email = 'no-reply.7@shopsys.com';
        $userData->password = 'no-reply.7';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply.8@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Derick';
        $userData->lastName = 'Ansah';
        $userData->email = 'no-reply.8@shopsys.com';
        $userData->password = 'no-reply.8';
        $billingAddressData->companyCustomer = false;
        $billingAddressData->city = 'Opava';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);
        $customerData->userData = $userData;
        $customerData->billingAddressData = $billingAddressData;
        $customersData[] = $customerData;

        // no-reply@shopsys.com
        $customerData = $this->customerDataFactory->create();
        $userData = $this->userDataFactory->createForDomainId($domainId);
        $billingAddressData = $this->billingAddressDataFactory->create();
        $userData->firstName = 'Johny';
        $userData->lastName = 'English';
        $userData->email = 'no-reply@shopsys.com';
        $userData->password = 'user123';
        $userData->telephone = '603123456';
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
        $resetPasswordHash = $this->hashGenerator->generateHash(CustomerPasswordFacade::RESET_PASSWORD_HASH_LENGTH);
        $customer->setResetPasswordHash($resetPasswordHash);
        $this->em->flush($customer);
    }
}
