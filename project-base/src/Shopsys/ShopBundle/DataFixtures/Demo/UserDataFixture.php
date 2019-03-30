<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface;

class UserDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    const USER_WITH_RESET_PASSWORD_HASH = 'user_with_reset_password_hash';
    const USERS_PER_DOMAIN = 7;
    const FIRST_DOMAIN_ID = 1;
    const SECOND_DOMAIN_ID = 2;
    const FAKER_SEED = 1;
    const FIRST_CUSTOMER_PASSWORD = 'user123';

    const ADDITIONAL_FAKER_LOCALES_BY_LOCALES = [
        'cs' => 'cs_CZ',
    ];

    /** @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade */
    protected $customerFacade;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $em;

    /** @var \Shopsys\FrameworkBundle\Component\String\HashGenerator */
    protected $hashGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface
     */
    protected $userDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface $customerDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface $userDataFactory
     */
    public function __construct(
        CustomerFacade $customerFacade,
        EntityManagerInterface $em,
        HashGenerator $hashGenerator,
        Domain $domain,
        CustomerDataFactoryInterface $customerDataFactory,
        UserDataFactoryInterface $userDataFactory
    ) {
        $this->customerFacade = $customerFacade;
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
        $this->domain = $domain;
        $this->customerDataFactory = $customerDataFactory;
        $this->userDataFactory = $userDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Country\Country[] $countries */
        $countries = [
            self::FIRST_DOMAIN_ID => $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
            self::SECOND_DOMAIN_ID => $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA),
        ];

        $fakerSeed = self::FAKER_SEED;
        $totalCounter = 0;
        $domainConfigs = $this->domain->getAll();
        foreach ($domainConfigs as $domainConfig) {
            $locale = $this->getFakerLocaleByLocale($domainConfig->getLocale());

            $faker = Factory::create($locale);
            $faker->seed($fakerSeed++);

            $domainId = $domainConfig->getId();
            for ($customerCount = 0; $customerCount < self::USERS_PER_DOMAIN; $customerCount++) {
                ++$totalCounter;
                $userData = $this->userDataFactory->createForDomainId($domainId);

                $userData->domainId = $domainId;
                $userData->firstName = $faker->firstName;
                $userData->lastName = $faker->lastName;
                $userData->createdAt = $faker->dateTimeBetween('-1 week', 'now');
                $userData->email = 'no-reply' . $totalCounter . '@shopsys.com';
                $userData->password = $faker->password;
                $userData->telephone = $faker->phoneNumber;

                if ((int)$customerCount === 0 && (int)$domainId === 1) {
                    $userData->password = self::FIRST_CUSTOMER_PASSWORD;
                }

                $customerData = $this->customerDataFactory->create();
                $customerData->userData = $userData;

                $customerData->billingAddressData->companyCustomer = $faker->boolean();
                if ($customerData->billingAddressData->companyCustomer === true) {
                    $customerData->billingAddressData->companyName = $faker->company;
                    $customerData->billingAddressData->companyNumber = $faker->randomNumber(6);
                    $customerData->billingAddressData->companyTaxNumber = $faker->randomNumber(6);
                }

                $customerData->deliveryAddressData->country = array_key_exists($domainId, $countries) ? $countries[$domainId] : $countries[self::FIRST_DOMAIN_ID];
                $customerData->deliveryAddressData->city = $faker->city;
                $customerData->deliveryAddressData->postcode = $faker->citySuffix;
                $customerData->deliveryAddressData->street = $faker->streetAddress;
                $customerData->deliveryAddressData->postcode = $faker->postcode;

                $customer = $this->customerFacade->create($customerData);

                if ($customer->getId() === 1) {
                    $this->resetPassword($customer);
                    $this->addReference(self::USER_WITH_RESET_PASSWORD_HASH, $customer);
                }
            }
        }
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getFakerLocaleByLocale(string $locale): string
    {
        if (array_key_exists($locale, self::ADDITIONAL_FAKER_LOCALES_BY_LOCALES)) {
            return self::ADDITIONAL_FAKER_LOCALES_BY_LOCALES[$locale];
        }

        return Factory::DEFAULT_LOCALE;
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $customer
     */
    protected function resetPassword(User $customer)
    {
        $customer->resetPassword($this->hashGenerator);
        $this->em->flush($customer);
    }
}
