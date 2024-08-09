<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Customer;

use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Tests\App\Test\TransactionFunctionalTestCase;

class UserFacadeTest extends TransactionFunctionalTestCase
{
    protected const EXISTING_EMAIL_ON_DOMAIN_1 = 'no-reply.3@shopsys.com';
    protected const EXISTING_EMAIL_ON_DOMAIN_2 = 'no-reply.4@shopsys.com';

    /**
     * @inject
     */
    protected CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    protected CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory;

    public function testChangeEmailToExistingEmailButDifferentDomainDoNotThrowException()
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            self::EXISTING_EMAIL_ON_DOMAIN_1,
            Domain::FIRST_DOMAIN_ID,
        );
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserUpdateData->customerUserData->email = self::EXISTING_EMAIL_ON_DOMAIN_2;

        $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateNotDuplicateEmail()
    {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->customerUserData->pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );
        $customerUserUpdateData->customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserUpdateData->customerUserData->email = 'unique-email@shopsys.com';
        $customerUserUpdateData->customerUserData->firstName = 'John';
        $customerUserUpdateData->customerUserData->lastName = 'Doe';
        $customerUserUpdateData->customerUserData->password = 'password';

        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = false;
        $billingAddressData->street = '123 Fake Street';
        $billingAddressData->city = 'Springfield';
        $billingAddressData->postcode = '12345';
        $billingAddressData->country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);
        $customerUserUpdateData->billingAddressData = $billingAddressData;

        $this->customerUserFacade->create($customerUserUpdateData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateDuplicateEmail()
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            self::EXISTING_EMAIL_ON_DOMAIN_1,
            Domain::FIRST_DOMAIN_ID,
        );
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserUpdateData->customerUserData->password = 'password';
        $customerUserUpdateData->billingAddressData->uuid = '683213c6-8879-4b65-a429-b5f17c98ac96';
        $this->expectException(DuplicateEmailException::class);

        $this->customerUserFacade->create($customerUserUpdateData);
    }

    public function testCreateDuplicateEmailCaseInsentitive()
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            self::EXISTING_EMAIL_ON_DOMAIN_1,
            Domain::FIRST_DOMAIN_ID,
        );
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserUpdateData->customerUserData->password = 'password';
        $customerUserUpdateData->customerUserData->email = mb_strtoupper(self::EXISTING_EMAIL_ON_DOMAIN_1);
        $customerUserUpdateData->billingAddressData->uuid = 'c82523e6-47bb-4681-bcb7-39e42f77fd19';
        $this->expectException(DuplicateEmailException::class);

        $this->customerUserFacade->create($customerUserUpdateData);
    }
}
