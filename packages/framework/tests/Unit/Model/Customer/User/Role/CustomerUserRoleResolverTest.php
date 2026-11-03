<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer\User\Role;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleHierarchyProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Symfony\Bundle\SecurityBundle\Security;

class CustomerUserRoleResolverTest extends TestCase
{
    protected const int FIRST_CUSTOMER_ID = 1;
    protected const int SECOND_CUSTOMER_ID = 2;

    protected int $lastEntityId = 0;

    /**
     * @param string[] $customerUserRoles
     * @param string[] $rolesToAssign
     */
    #[DataProvider('getCanCustomerUserAssignRoleGroupData')]
    public function testCanCustomerUserAssignRoleGroup(
        array $customerUserRoles,
        array $rolesToAssign,
        bool $expectedResult,
    ): void {
        $customerUserRoleResolver = $this->createCustomerUserRoleResolver();
        $customerUser = $this->createCustomerUserStub($customerUserRoles, self::FIRST_CUSTOMER_ID);

        $this->assertSame(
            $expectedResult,
            $customerUserRoleResolver->canCustomerUserAssignRoleGroup($customerUser, $this->createRoleGroupStub($rolesToAssign)),
        );
    }

    /**
     * @return iterable<string, array{customerUserRoles: string[], rolesToAssign: string[], expectedResult: bool}>
     */
    public static function getCanCustomerUserAssignRoleGroupData(): iterable
    {
        yield 'all roles can assign all roles' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_ALL],
            'rolesToAssign' => [CustomerUserRole::ROLE_API_ALL],
            'expectedResult' => true,
        ];

        yield 'all roles can assign individual roles' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_ALL],
            'rolesToAssign' => [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE, CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES],
            'expectedResult' => true,
        ];

        yield 'customer manager can assign own roles' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'rolesToAssign' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'expectedResult' => true,
        ];

        yield 'customer manager can assign no roles' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'rolesToAssign' => [],
            'expectedResult' => true,
        ];

        yield 'customer manager can assign self manage included in managing customers' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'rolesToAssign' => [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE],
            'expectedResult' => true,
        ];

        yield 'customer manager cannot assign all roles' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'rolesToAssign' => [CustomerUserRole::ROLE_API_ALL],
            'expectedResult' => false,
        ];

        yield 'customer manager cannot assign roles they do not have' => [
            'customerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'rolesToAssign' => [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE, CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES],
            'expectedResult' => false,
        ];

        yield 'every individual role cannot assign all roles' => [
            'customerUserRoles' => self::getAllIndividualRoles(),
            'rolesToAssign' => [CustomerUserRole::ROLE_API_ALL],
            'expectedResult' => false,
        ];
    }

    /**
     * @param string[] $changingCustomerUserRoles
     * @param string[] $changedCustomerUserRoles
     */
    #[DataProvider('getCanCustomerUserChangeRoleGroupOfCustomerUserData')]
    public function testCanCustomerUserChangeRoleGroupOfCustomerUser(
        array $changingCustomerUserRoles,
        array $changedCustomerUserRoles,
        int $changedCustomerId,
        bool $expectedResult,
    ): void {
        $customerUserRoleResolver = $this->createCustomerUserRoleResolver();
        $changingCustomerUser = $this->createCustomerUserStub($changingCustomerUserRoles, self::FIRST_CUSTOMER_ID);
        $changedCustomerUser = $this->createCustomerUserStub($changedCustomerUserRoles, $changedCustomerId);

        $this->assertSame(
            $expectedResult,
            $customerUserRoleResolver->canCustomerUserChangeRoleGroupOfCustomerUser($changingCustomerUser, $changedCustomerUser),
        );
    }

    /**
     * @return iterable<string, array{changingCustomerUserRoles: string[], changedCustomerUserRoles: string[], changedCustomerId: int, expectedResult: bool}>
     */
    public static function getCanCustomerUserChangeRoleGroupOfCustomerUserData(): iterable
    {
        yield 'customer manager can change role group of weaker customer user of the same customer' => [
            'changingCustomerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'changedCustomerUserRoles' => [],
            'changedCustomerId' => self::FIRST_CUSTOMER_ID,
            'expectedResult' => true,
        ];

        yield 'customer manager cannot change role group of stronger customer user of the same customer' => [
            'changingCustomerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'changedCustomerUserRoles' => [CustomerUserRole::ROLE_API_ALL],
            'changedCustomerId' => self::FIRST_CUSTOMER_ID,
            'expectedResult' => false,
        ];

        yield 'customer manager cannot change role group of customer user of another customer' => [
            'changingCustomerUserRoles' => [CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS],
            'changedCustomerUserRoles' => [],
            'changedCustomerId' => self::SECOND_CUSTOMER_ID,
            'expectedResult' => false,
        ];

        yield 'customer user without manage customers role cannot change role group of weaker customer user' => [
            'changingCustomerUserRoles' => [CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE],
            'changedCustomerUserRoles' => [],
            'changedCustomerId' => self::FIRST_CUSTOMER_ID,
            'expectedResult' => false,
        ];

        yield 'all roles can change role group of customer user with all roles' => [
            'changingCustomerUserRoles' => [CustomerUserRole::ROLE_API_ALL],
            'changedCustomerUserRoles' => [CustomerUserRole::ROLE_API_ALL],
            'changedCustomerId' => self::FIRST_CUSTOMER_ID,
            'expectedResult' => true,
        ];
    }

    /**
     * @return string[]
     */
    protected static function getAllIndividualRoles(): array
    {
        return [
            CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE,
            CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS,
            CustomerUserRole::ROLE_API_MANAGE_COMPANY_DATA,
            CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES,
            CustomerUserRole::ROLE_API_CART_AND_ORDER_CREATION,
            CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW,
            CustomerUserRole::ROLE_API_COMPLAINT_CREATION,
            CustomerUserRole::ROLE_API_COMPANY_COMPLAINTS_VIEW,
        ];
    }

    protected function createCustomerUserRoleResolver(): CustomerUserRoleResolver
    {
        $roles = [new Role(CustomerUserRole::ROLE_API_ALL, CustomerUserRole::ROLE_API_ALL)];

        foreach (self::getAllIndividualRoles() as $individualRole) {
            $roles[] = new Role($individualRole, $individualRole);
        }

        $roleRegistryStub = $this->createStub(RoleRegistryInterface::class);
        $roleRegistryStub->method('getRoles')->willReturn($roles);

        return new CustomerUserRoleResolver(
            new CustomerUserRoleHierarchyProvider($roleRegistryStub),
            $this->createStub(Security::class),
            new InMemoryCache(),
        );
    }

    /**
     * @param string[] $roles
     */
    protected function createCustomerUserStub(array $roles, int $customerId): CustomerUser
    {
        $customerStub = $this->createStub(Customer::class);
        $customerStub->method('getId')->willReturn($customerId);

        $customerUserStub = $this->createStub(CustomerUser::class);
        $customerUserStub->method('getId')->willReturn($this->getNextEntityId());
        $customerUserStub->method('getRoles')->willReturn($roles);
        $customerUserStub->method('getCustomer')->willReturn($customerStub);
        $customerUserStub->method('getRoleGroup')->willReturn($this->createRoleGroupStub($roles));

        return $customerUserStub;
    }

    /**
     * @param string[] $roles
     */
    protected function createRoleGroupStub(array $roles): CustomerUserRoleGroup
    {
        $roleGroupStub = $this->createStub(CustomerUserRoleGroup::class);
        $roleGroupStub->method('getId')->willReturn($this->getNextEntityId());
        $roleGroupStub->method('getRoles')->willReturn($roles);

        return $roleGroupStub;
    }

    protected function getNextEntityId(): int
    {
        return ++$this->lastEntityId;
    }
}
