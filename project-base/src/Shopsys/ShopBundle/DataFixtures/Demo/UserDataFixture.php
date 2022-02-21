<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\HashGenerator;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\User;

class UserDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const USER_WITH_RESET_PASSWORD_HASH = 'user_with_reset_password_hash';

    /** @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade */
    protected $customerFacade;

    /** @var \Shopsys\ShopBundle\DataFixtures\Demo\UserDataFixtureLoader */
    protected $loaderService;

    /** @var \Faker\Generator */
    protected $faker;

    /** @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator */
    protected $em;

    /** @var \Shopsys\FrameworkBundle\Component\String\HashGenerator */
    protected $hashGenerator;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\ShopBundle\DataFixtures\Demo\UserDataFixtureLoader $loaderService
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        CustomerFacade $customerFacade,
        UserDataFixtureLoader $loaderService,
        Generator $faker,
        EntityManagerInterface $em,
        HashGenerator $hashGenerator
    ) {
        $this->customerFacade = $customerFacade;
        $this->loaderService = $loaderService;
        $this->faker = $faker;
        $this->em = $em;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $manager
     */
    public function load(ObjectManager $manager)
    {
        $countries = [
            $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC),
            $this->getReference(CountryDataFixture::COUNTRY_SLOVAKIA),
        ];
        $this->loaderService->injectReferences($countries);

        $customersData = $this->loaderService->getCustomersDataByDomainId(Domain::FIRST_DOMAIN_ID);

        foreach ($customersData as $customerData) {
            $customerData->userData->createdAt = $this->faker->dateTimeBetween('-1 week', 'now');

            $customer = $this->customerFacade->create($customerData);

            if ($customer->getId() === 1) {
                $this->resetPassword($customer);
                $this->addReference(self::USER_WITH_RESET_PASSWORD_HASH, $customer);
            }
        }
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
