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
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface
     * @inject
     */
    protected $customerUserDataFactory;

    public function testChangeEmailToExistingEmailButDifferentDomainDoNotThrowException()
    {
        $user = $this->userFacade->findUserByEmailAndDomain(self::EXISTING_EMAIL_ON_DOMAIN_1, Domain::FIRST_DOMAIN_ID);
        $customerUserData = $this->customerUserDataFactory->createFromUser($user);
        $customerUserData->userData->email = self::EXISTING_EMAIL_ON_DOMAIN_2;

        $this->userFacade->editByAdmin($user->getId(), $customerUserData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateNotDuplicateEmail()
    {
        $customerUserData = $this->customerUserDataFactory->create();
        $customerUserData->userData->pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        $customerUserData->userData->domainId = 1;
        $customerUserData->userData->email = 'unique-email@shopsys.com';
        $customerUserData->userData->firstName = 'John';
        $customerUserData->userData->lastName = 'Doe';
        $customerUserData->userData->password = 'password';

        $this->userFacade->create($customerUserData);

        $this->expectNotToPerformAssertions();
    }

    public function testCreateDuplicateEmail()
    {
        $user = $this->userFacade->findUserByEmailAndDomain(self::EXISTING_EMAIL_ON_DOMAIN_1, 1);
        $customerUserData = $this->customerUserDataFactory->createFromUser($user);
        $customerUserData->userData->password = 'password';
        $this->expectException(\Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailUserException::class);

        $this->userFacade->create($customerUserData);
    }

    public function testCreateDuplicateEmailCaseInsentitive()
    {
        $user = $this->userFacade->findUserByEmailAndDomain(self::EXISTING_EMAIL_ON_DOMAIN_1, 1);
        $customerUserData = $this->customerUserDataFactory->createFromUser($user);
        $customerUserData->userData->password = 'password';
        $customerUserData->userData->email = mb_strtoupper(self::EXISTING_EMAIL_ON_DOMAIN_1);
        $this->expectException(\Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailUserException::class);

        $this->userFacade->create($customerUserData);
    }
}
