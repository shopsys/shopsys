<?php

namespace Shopsys\FrameworkBundle\DataFixtures\Performance;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DataFixtures\Demo\CountryDataFixture;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserDataFixture
{
    const FIRST_PERFORMANCE_USER = 'first_performance_user';

    /**
     * @var int
     */
    private $userCountPerDomain;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    private $sqlLoggerFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     */
    private $customerEditFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface
     */
    private $userDataFactory;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface
     */
    private $customerDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface
     */
    private $billingAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface
     */
    private $deliveryAddressDataFactory;

    /**
     * @param int $userCountPerDomain
     * @param \Faker\Generator $faker
     */
    public function __construct(
        $userCountPerDomain,
        EntityManagerInterface $em,
        Domain $domain,
        SqlLoggerFacade $sqlLoggerFacade,
        CustomerFacade $customerEditFacade,
        UserDataFactoryInterface $userDataFactory,
        Faker $faker,
        PersistentReferenceFacade $persistentReferenceFacade,
        ProgressBarFactory $progressBarFactory,
        CustomerDataFactoryInterface $customerDataFactory,
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
    ) {
        $this->em = $em;
        $this->domain = $domain;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->customerEditFacade = $customerEditFacade;
        $this->userDataFactory = $userDataFactory;
        $this->faker = $faker;
        $this->persistentReferenceFacade = $persistentReferenceFacade;
        $this->userCountPerDomain = $userCountPerDomain;
        $this->progressBarFactory = $progressBarFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->deliveryAddressDataFactory = $deliveryAddressDataFactory;
    }

    public function load(OutputInterface $output)
    {
        // Sql logging during mass data import makes memory leak
        $this->sqlLoggerFacade->temporarilyDisableLogging();
        $domains = $this->domain->getAll();

        $progressBar = $this->progressBarFactory->create($output, count($domains) * $this->userCountPerDomain);

        $isFirstUser = true;

        foreach ($domains as $domainConfig) {
            for ($i = 0; $i < $this->userCountPerDomain; $i++) {
                $user = $this->createCustomerOnDomain($domainConfig->getId(), $i);
                $progressBar->advance();

                if ($isFirstUser) {
                    $this->persistentReferenceFacade->persistReference(self::FIRST_PERFORMANCE_USER, $user);
                    $isFirstUser = false;
                }

                $this->em->clear();
            }
        }

        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param int $domainId
     * @param int $userNumber
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    private function createCustomerOnDomain($domainId, $userNumber)
    {
        $customerData = $this->getRandomCustomerDataByDomainId($domainId, $userNumber);

        return $this->customerEditFacade->create($customerData);
    }

    /**
     * @param int $domainId
     * @param int $userNumber
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    private function getRandomCustomerDataByDomainId($domainId, $userNumber)
    {
        $customerData = $this->customerDataFactory->create();

        $country = $this->persistentReferenceFacade->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC_1);

        $userData = $this->userDataFactory->createForDomainId($domainId);
        $userData->firstName = $this->faker->firstName;
        $userData->lastName = $this->faker->lastName;
        $userData->email = $userNumber . '.' . $this->faker->safeEmail;
        $userData->password = $this->faker->password;
        $userData->domainId = $domainId;
        $userData->createdAt = $this->faker->dateTimeBetween('-1 year', 'now');

        $customerData->userData = $userData;

        $billingAddressData = $this->billingAddressDataFactory->create();
        $billingAddressData->companyCustomer = $this->faker->boolean();
        if ($billingAddressData->companyCustomer === true) {
            $billingAddressData->companyName = $this->faker->company;
            $billingAddressData->companyNumber = $this->faker->randomNumber(6);
            $billingAddressData->companyTaxNumber = $this->faker->randomNumber(6);
        }
        $billingAddressData->street = $this->faker->streetAddress;
        $billingAddressData->city = $this->faker->city;
        $billingAddressData->postcode = $this->faker->postcode;
        $billingAddressData->country = $country;
        $billingAddressData->telephone = $this->faker->phoneNumber;
        $customerData->billingAddressData = $billingAddressData;

        $deliveryAddressData = $this->deliveryAddressDataFactory->create();
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->city = $this->faker->city;
        $deliveryAddressData->companyName = $this->faker->company;
        $deliveryAddressData->firstName = $this->faker->firstName;
        $deliveryAddressData->lastName = $this->faker->lastName;
        $deliveryAddressData->postcode = $this->faker->postcode;
        $deliveryAddressData->country = $country;
        $deliveryAddressData->street = $this->faker->streetAddress;
        $deliveryAddressData->telephone = $this->faker->phoneNumber;
        $customerData->deliveryAddressData = $deliveryAddressData;

        return $customerData;
    }
}
