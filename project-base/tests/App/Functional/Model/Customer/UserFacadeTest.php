<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Customer;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;

class UserFacadeTest extends TransactionFunctionalTestCase
{
    protected const EXISTING_EMAIL_ON_DOMAIN_1 = 'no-reply.3@shopsys.com';
    protected const EXISTING_EMAIL_ON_DOMAIN_2 = 'no-reply.4@shopsys.com';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserFacade
     * @inject
     */
    protected $userFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateDataFactoryInterface
     * @inject
     */
    protected $customerUserUpdateDataFactory;

    public function testChangeEmailToExistingEmailButDifferentDomainDoNotThrowException()
    {
        $user = $this->userFacade->findUserByEmailAndDomain(self::EXISTING_EMAIL_ON_DOMAIN_1, Domain::FIRST_DOMAIN_ID);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromUser($user);
        $customerUserUpdateData->userData->email = self::EXISTING_EMAIL_ON_DOMAIN_2;

        $this->userFacade->editByAdmin($user->getId(), $customerUserUpdateData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateNotDuplicateEmail()
    {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->userData->pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        $customerUserUpdateData->userData->domainId = 1;
        $customerUserUpdateData->userData->email = 'unique-email@shopsys.com';
        $customerUserUpdateData->userData->firstName = 'John';
        $customerUserUpdateData->userData->lastName = 'Doe';
        $customerUserUpdateData->userData->password = 'password';

        $this->userFacade->create($customerUserUpdateData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateDuplicateEmail()
    {
        $user = $this->userFacade->findUserByEmailAndDomain(self::EXISTING_EMAIL_ON_DOMAIN_1, 1);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromUser($user);
        $customerUserUpdateData->userData->password = 'password';
        $this->expectException(\Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException::class);

        $this->userFacade->create($customerUserUpdateData);
    }

    public function testCreateDuplicateEmailCaseInsentitive()
    {
        $user = $this->userFacade->findUserByEmailAndDomain(self::EXISTING_EMAIL_ON_DOMAIN_1, 1);
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromUser($user);
        $customerUserUpdateData->userData->password = 'password';
        $customerUserUpdateData->userData->email = mb_strtoupper(self::EXISTING_EMAIL_ON_DOMAIN_1);
        $this->expectException(\Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException::class);

        $this->userFacade->create($customerUserUpdateData);
    }
}
