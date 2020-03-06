<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Customer;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class UserFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const EXISTING_EMAIL_ON_DOMAIN_1 = 'no-reply.3@shopsys.com';
    protected const EXISTING_EMAIL_ON_DOMAIN_2 = 'no-reply.4@shopsys.com';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     * @inject
     */
    protected $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     * @inject
     */
    protected $customerUserUpdateDataFactory;

    public function testChangeEmailToExistingEmailButDifferentDomainDoNotThrowException()
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            self::EXISTING_EMAIL_ON_DOMAIN_1,
            Domain::FIRST_DOMAIN_ID
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
            Domain::FIRST_DOMAIN_ID
        );
        $customerUserUpdateData->customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserUpdateData->customerUserData->email = 'unique-email@shopsys.com';
        $customerUserUpdateData->customerUserData->firstName = 'John';
        $customerUserUpdateData->customerUserData->lastName = 'Doe';
        $customerUserUpdateData->customerUserData->password = 'password';

        $this->customerUserFacade->create($customerUserUpdateData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateDuplicateEmail()
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            self::EXISTING_EMAIL_ON_DOMAIN_1,
            Domain::FIRST_DOMAIN_ID
        );
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserUpdateData->customerUserData->password = 'password';
        $this->expectException(\Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException::class);

        $this->customerUserFacade->create($customerUserUpdateData);
    }

    public function testCreateDuplicateEmailCaseInsentitive()
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            self::EXISTING_EMAIL_ON_DOMAIN_1,
            Domain::FIRST_DOMAIN_ID
        );
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
        $customerUserUpdateData->customerUserData->password = 'password';
        $customerUserUpdateData->customerUserData->email = mb_strtoupper(self::EXISTING_EMAIL_ON_DOMAIN_1);
        $this->expectException(\Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException::class);

        $this->customerUserFacade->create($customerUserUpdateData);
    }
}
