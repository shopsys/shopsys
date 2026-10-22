<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAssignable;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\CompanyOwnerTestHelper;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

final class CustomerUserCustomerManagerTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_CUSTOMER_MANAGER_EMAIL;

    private const string NEW_CUSTOMER_USER_EMAIL = 'new-customer-user-by-manager@shopsys.com';

    /**
     * @inject
     */
    private CompanyOwnerTestHelper $companyOwnerTestHelper;

    public function testCustomerUserRoleGroupsReturnsOnlyAssignableRoleGroups(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CustomerUserRoleGroupsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'customerUserRoleGroups');

        $expectedRoleGroupUuids = [
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CUSTOMER_MANAGER)->getUuid(),
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CATALOG_USER)->getUuid(),
        ];
        $actualRoleGroupUuids = array_column($responseData, 'uuid');

        sort($expectedRoleGroupUuids);
        sort($actualRoleGroupUuids);

        $this->assertSame($expectedRoleGroupUuids, $actualRoleGroupUuids);
    }

    public function testAddNewCustomerUserWithOwnerRoleGroupIsNotAllowed(): void
    {
        $response = $this->addNewCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER);

        $this->assertRoleGroupNotAssignableValidationError($response);
        $this->assertNull($this->customerUserFacade->findCustomerUserByEmailAndDomain(self::NEW_CUSTOMER_USER_EMAIL, $this->domain->getId()));
    }

    public function testAddNewCustomerUserWithCatalogUserRoleGroup(): void
    {
        $response = $this->addNewCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CATALOG_USER);
        $responseData = $this->getResponseDataForGraphQlType($response, 'AddNewCustomerUser');

        $this->assertSame(
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CATALOG_USER)->getUuid(),
            $responseData['roleGroup']['uuid'],
        );
    }

    public function testEditOwnRoleGroupToOwnerIsNotAllowedWhenCompanyHasAnotherOwner(): void
    {
        $this->companyOwnerTestHelper->makeCustomerUserAnotherCompanyOwner(
            $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL),
        );

        $currentCustomerUser = $this->getCustomerUserByDefaultCredentials();

        $response = $this->editCustomerUser($currentCustomerUser, CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER);

        $this->assertRoleGroupNotAssignableValidationError($response);
        $this->assertSame(
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CUSTOMER_MANAGER)->getUuid(),
            $this->customerUserFacade->getCustomerUserById($currentCustomerUser->getId())->getRoleGroup()->getUuid(),
        );
    }

    public function testEditOwnerIsNotAllowed(): void
    {
        $owner = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL);

        $response = $this->editCustomerUser($owner, CustomerUserRoleGroupDataFixture::ROLE_GROUP_CATALOG_USER);

        $this->assertAccessDeniedError($response);
        $this->assertSame(
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER)->getUuid(),
            $this->customerUserFacade->getCustomerUserById($owner->getId())->getRoleGroup()->getUuid(),
        );
    }

    public function testEditCustomerUserWithStrongerRoleGroupIsNotAllowed(): void
    {
        $customerUser = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_USER_EMAIL);

        $response = $this->editCustomerUser($customerUser, CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER);

        $this->assertAccessDeniedError($response);
    }

    public function testRemoveOwnerIsNotAllowed(): void
    {
        $owner = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/RemoveCustomerUserMutation.graphql', [
            'customerUserUuid' => $owner->getUuid(),
        ]);

        $this->assertAccessDeniedError($response);
        $this->assertSame($owner->getId(), $this->customerUserFacade->getByUuid($owner->getUuid())->getId());
    }

    public function testEditCatalogUserToCustomerManagerRoleGroup(): void
    {
        $catalogUser = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL);
        $customerManagerRoleGroupUuid = $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CUSTOMER_MANAGER)->getUuid();

        $response = $this->editCustomerUser($catalogUser, CustomerUserRoleGroupDataFixture::ROLE_GROUP_CUSTOMER_MANAGER);
        $responseData = $this->getResponseDataForGraphQlType($response, 'EditCustomerUserPersonalData');

        $this->assertSame($customerManagerRoleGroupUuid, $responseData['roleGroup']['uuid']);
        $this->assertSame(
            $customerManagerRoleGroupUuid,
            $this->customerUserFacade->getCustomerUserById($catalogUser->getId())->getRoleGroup()->getUuid(),
        );
    }

    public function testCanBeManagedByCurrentCustomerUser(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CustomerUsersManageabilityQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'customerUsers');

        $expectedManageabilityByEmail = [
            CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL => false,
            CompanyDataFixture::B2B_COMPANY_USER_EMAIL => false,
            CompanyDataFixture::B2B_COMPANY_CUSTOMER_MANAGER_EMAIL => true,
            CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL => false,
            CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL => true,
            CompanyDataFixture::B2B_COMPANY_ACCOUNTANT_EMAIL => false,
        ];
        $actualManageabilityByEmail = array_column($responseData, 'canBeManagedByCurrentCustomerUser', 'email');

        ksort($expectedManageabilityByEmail);
        ksort($actualManageabilityByEmail);

        $this->assertSame($expectedManageabilityByEmail, $actualManageabilityByEmail);
    }

    /**
     * @return array<string, mixed>
     */
    private function addNewCustomerUser(string $roleGroupReferenceName): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/AddNewCustomerUserMutation.graphql', [
            'firstName' => 'First name',
            'lastName' => 'Last name',
            'email' => self::NEW_CUSTOMER_USER_EMAIL,
            'roleGroupUuid' => $this->getRoleGroup($roleGroupReferenceName)->getUuid(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function editCustomerUser(CustomerUser $customerUser, string $roleGroupReferenceName): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $customerUser->getUuid(),
            'firstName' => 'First name',
            'lastName' => 'Last name',
            'roleGroupUuid' => $this->getRoleGroup($roleGroupReferenceName)->getUuid(),
        ]);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function assertRoleGroupNotAssignableValidationError(array $response): void
    {
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertSame(
            CustomerUserRoleGroupAssignable::CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE,
            $validationErrors['input.roleGroupUuid'][0]['code'],
        );
    }

    private function getCustomerUser(string $email): CustomerUser
    {
        return $this->getReferenceForDomain($email, $this->domain->getId(), CustomerUser::class);
    }

    private function getRoleGroup(string $referenceName): CustomerUserRoleGroup
    {
        return $this->getReference($referenceName, CustomerUserRoleGroup::class);
    }
}
