<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\Constraints;

use Override;
use PHPUnit\Framework\MockObject\Stub;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CurrentCustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository;
use Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupChangeAllowed;
use Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupChangeAllowedValidator;
use stdClass;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomerUserRoleGroupChangeAllowedValidatorTest extends ConstraintValidatorTestCase
{
    protected const string CURRENT_ROLE_GROUP_UUID = '7c1b6d0e-41f4-4c6b-9d0e-3c2a4d8a1f10';
    protected const string ROLE_GROUP_TO_ASSIGN_UUID = 'a2e5f6b4-8d3c-4e1f-b7a9-0c5d2e4f6a81';

    protected CurrentCustomerUser&Stub $currentCustomerUserStub;

    protected CurrentCustomerUserRoleResolver&Stub $currentCustomerUserRoleResolverStub;

    #[Override]
    protected function setUp(): void
    {
        $this->currentCustomerUserStub = $this->createStub(CurrentCustomerUser::class);
        $this->currentCustomerUserRoleResolverStub = $this->createStub(CurrentCustomerUserRoleResolver::class);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function createValidator(): ConstraintValidatorInterface
    {
        $roleGroupToAssign = $this->createRoleGroupStub(self::ROLE_GROUP_TO_ASSIGN_UUID);

        $customerUserRoleGroupRepositoryStub = $this->createStub(CustomerUserRoleGroupRepository::class);
        $customerUserRoleGroupRepositoryStub->method('getByUuid')->willReturn($roleGroupToAssign);

        return new CustomerUserRoleGroupChangeAllowedValidator(
            $this->currentCustomerUserStub,
            $this->createStub(CustomerUserFacade::class),
            $customerUserRoleGroupRepositoryStub,
            $this->currentCustomerUserRoleResolverStub,
        );
    }

    public function testRoleGroupChangeOfCurrentCustomerUserWithoutCustomerUserUuidIsValidatedAgainstCurrentCustomerUser(): void
    {
        $this->currentCustomerUserStub->method('findCurrentCustomerUser')->willReturn($this->createCustomerUserStub(self::CURRENT_ROLE_GROUP_UUID));
        $this->currentCustomerUserRoleResolverStub->method('canCurrentCustomerUserChangeRoleGroupOfCustomerUser')->willReturn(true);
        $this->currentCustomerUserRoleResolverStub->method('canCurrentCustomerUserAssignRoleGroup')->willReturn(false);

        $this->validator->validate($this->createInput(null), new CustomerUserRoleGroupChangeAllowed());

        $this->assertRoleGroupChangeNotAllowedViolationRaised();
    }

    public function testAssignableRoleGroupOfCurrentCustomerUserWithoutCustomerUserUuidIsValid(): void
    {
        $this->currentCustomerUserStub->method('findCurrentCustomerUser')->willReturn($this->createCustomerUserStub(self::CURRENT_ROLE_GROUP_UUID));
        $this->currentCustomerUserRoleResolverStub->method('canCurrentCustomerUserChangeRoleGroupOfCustomerUser')->willReturn(true);
        $this->currentCustomerUserRoleResolverStub->method('canCurrentCustomerUserAssignRoleGroup')->willReturn(true);

        $this->validator->validate($this->createInput(null), new CustomerUserRoleGroupChangeAllowed());

        $this->assertNoViolation();
    }

    public function testUnchangedRoleGroupOfCurrentCustomerUserWithoutCustomerUserUuidIsValid(): void
    {
        $this->currentCustomerUserStub->method('findCurrentCustomerUser')->willReturn($this->createCustomerUserStub(self::ROLE_GROUP_TO_ASSIGN_UUID));

        $this->validator->validate($this->createInput(null), new CustomerUserRoleGroupChangeAllowed());

        $this->assertNoViolation();
    }

    public function testRoleGroupChangeWithoutCustomerUserUuidAndWithoutCurrentCustomerUserIsNotAllowed(): void
    {
        $this->currentCustomerUserStub->method('findCurrentCustomerUser')->willReturn(null);

        $this->validator->validate($this->createInput(null), new CustomerUserRoleGroupChangeAllowed());

        $this->assertRoleGroupChangeNotAllowedViolationRaised();
    }

    protected function assertRoleGroupChangeNotAllowedViolationRaised(): void
    {
        $this->buildViolation((new CustomerUserRoleGroupChangeAllowed())->message)
            ->atPath('property.path.roleGroupUuid')
            ->setCode(CustomerUserRoleGroupChangeAllowed::CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED)
            ->assertRaised();
    }

    protected function createInput(?string $customerUserUuid): stdClass
    {
        $input = new stdClass();
        $input->customerUserUuid = $customerUserUuid;
        $input->roleGroupUuid = self::ROLE_GROUP_TO_ASSIGN_UUID;

        return $input;
    }

    protected function createCustomerUserStub(string $roleGroupUuid): CustomerUser
    {
        $customerUserStub = $this->createStub(CustomerUser::class);
        $customerUserStub->method('getRoleGroup')->willReturn($this->createRoleGroupStub($roleGroupUuid));

        return $customerUserStub;
    }

    protected function createRoleGroupStub(string $uuid): CustomerUserRoleGroup
    {
        $roleGroupStub = $this->createStub(CustomerUserRoleGroup::class);
        $roleGroupStub->method('getUuid')->willReturn($uuid);

        return $roleGroupStub;
    }
}
