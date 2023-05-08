<?php

declare(strict_types=1);

namespace App\DataFixtures\Performance;

use App\DataFixtures\Demo\CountryDataFixture;
use App\Model\Customer\User\CustomerUserDataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Generator as Faker;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerUserDataFixture
{
    public const FIRST_PERFORMANCE_USER = 'first_performance_user';

    private int $userCountPerDomain;

    private CustomerUserDataFactory $customerUserDataFactory;

    /**
     * @param int $userCountPerDomain
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserEditFacade
     * @param \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     * @param \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     */
    public function __construct(
        $userCountPerDomain,
        private readonly EntityManagerInterface $em,
        private readonly Domain $domain,
        private readonly SqlLoggerFacade $sqlLoggerFacade,
        private readonly CustomerUserFacade $customerUserEditFacade,
        CustomerUserDataFactoryInterface $customerUserDataFactory,
        private readonly Faker $faker,
        private readonly PersistentReferenceFacade $persistentReferenceFacade,
        private readonly ProgressBarFactory $progressBarFactory,
        private readonly CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
        private readonly DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory,
    ) {
        $this->customerUserDataFactory = $customerUserDataFactory;
        $this->userCountPerDomain = $userCountPerDomain;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function load(OutputInterface $output)
    {
        // Sql logging during mass data import makes memory leak
        $this->sqlLoggerFacade->temporarilyDisableLogging();
        $domains = $this->domain->getAll();

        $progressBar = $this->progressBarFactory->create($output, count($domains) * $this->userCountPerDomain);

        $isFirstCustomerUser = true;

        foreach ($domains as $domainConfig) {
            for ($i = 0; $i < $this->userCountPerDomain; $i++) {
                $customerUser = $this->createCustomerUserOnDomain($domainConfig->getId(), $i);
                $progressBar->advance();

                if ($isFirstCustomerUser) {
                    $this->persistentReferenceFacade->persistReference(self::FIRST_PERFORMANCE_USER, $customerUser);
                    $isFirstCustomerUser = false;
                }

                $this->em->clear();
            }
        }

        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param int $domainId
     * @param int $userNumber
     * @return \App\Model\Customer\User\CustomerUser
     */
    private function createCustomerUserOnDomain($domainId, $userNumber)
    {
        $customerUserUpdateData = $this->getRandomCustomerUserUpdateDataByDomainId($domainId, $userNumber);

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserEditFacade->create($customerUserUpdateData);

        return $customerUser;
    }

    /**
     * @param int $domainId
     * @param int $userNumber
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    private function getRandomCustomerUserUpdateDataByDomainId($domainId, $userNumber)
    {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $country = $this->persistentReferenceFacade->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC);

        $customerUserData = $this->customerUserDataFactory->createForDomainId($domainId);
        $customerUserData->firstName = $this->faker->firstName;
        $customerUserData->lastName = $this->faker->lastName;
        $customerUserData->email = $userNumber . '.' . $this->faker->safeEmail;
        $customerUserData->password = $this->faker->password;
        $customerUserData->domainId = $domainId;
        $customerUserData->createdAt = $this->faker->dateTimeBetween('-1 year', 'now');
        $customerUserData->telephone = $this->faker->phoneNumber;
        $customerUserData->customer = $customerUserUpdateData->billingAddressData->customer;
        $customerUserUpdateData->customerUserData = $customerUserData;

        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = $this->faker->boolean();

        if ($billingAddressData->companyCustomer === true) {
            $billingAddressData->companyName = $this->faker->company;
            $billingAddressData->companyNumber = (string)$this->faker->randomNumber(6);
            $billingAddressData->companyTaxNumber = (string)$this->faker->randomNumber(6);
        }
        $billingAddressData->street = $this->faker->streetAddress;
        $billingAddressData->city = $this->faker->city;
        $billingAddressData->postcode = $this->faker->postcode;
        $billingAddressData->country = $country;
        $customerUserUpdateData->billingAddressData = $billingAddressData;

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
        $customerUserUpdateData->deliveryAddressData = $deliveryAddressData;

        return $customerUserUpdateData;
    }
}
