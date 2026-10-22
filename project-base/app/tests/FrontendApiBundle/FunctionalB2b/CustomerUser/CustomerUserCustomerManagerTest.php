<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupAssignable;
use Shopsys\FrontendApiBundle\Component\Constraints\CustomerUserRoleGroupChangeAllowed;
use Tests\FrontendApiBundle\Test\CustomerUserRoleGroupTestHelper;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

final class CustomerUserCustomerManagerTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_CUSTOMER_MANAGER_EMAIL;

    private const string NEW_CUSTOMER_USER_EMAIL = 'new-customer-user-by-manager@shopsys.com';

    /**
     * @inject
     */
    private CustomerUserRoleGroupTestHelper $customerUserRoleGroupTestHelper;

    public function testCustomerUserRoleGroupsReturnsOnlyAssignableRoleGroups(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CustomerUserRoleGroupsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'customerUserRoleGroups');

        $expectedRoleGroupUuids = [
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CUSTOMER_MANAGER)->getUuid(),
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER)->getUuid(),
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER)->getUuid(),
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

        $this->assertValidationErrorOfRoleGroup($response, CustomerUserRoleGroupAssignable::CUSTOMER_USER_ROLE_GROUP_NOT_ASSIGNABLE);
        $this->assertNull($this->customerUserFacade->findCustomerUserByEmailAndDomain(self::NEW_CUSTOMER_USER_EMAIL, $this->domain->getId()));
    }

    public function testAddNewCustomerUserWithUserRoleGroup(): void
    {
        $response = $this->addNewCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER);
        $responseData = $this->getResponseDataForGraphQlType($response, 'AddNewCustomerUser');

        $this->assertSame(
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER)->getUuid(),
            $responseData['roleGroup']['uuid'],
        );
    }

    public function testEditOwnRoleGroupToOwnerIsNotAllowedWhenCompanyHasAnotherOwner(): void
    {
        $this->customerUserRoleGroupTestHelper->assignRoleGroup(
            $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL),
            $this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER),
        );

        $currentCustomerUser = $this->getCustomerUserByDefaultCredentials();

        $response = $this->editCustomerUser($currentCustomerUser, CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER);

        $this->assertValidationErrorOfRoleGroup($response, CustomerUserRoleGroupChangeAllowed::CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED);
        $this->assertRoleGroupOfCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_CUSTOMER_MANAGER, $currentCustomerUser);
    }

    public function testEditOwnerPersonalDataKeepingOwnerRoleGroup(): void
    {
        $owner = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL);
        $editedFirstName = 'Edited first name';

        $response = $this->editCustomerUser($owner, CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER, $editedFirstName);
        $responseData = $this->getResponseDataForGraphQlType($response, 'EditCustomerUserPersonalData');

        $this->assertSame($editedFirstName, $responseData['firstName']);
        $this->assertSame($editedFirstName, $this->customerUserFacade->getCustomerUserById($owner->getId())->getFirstName());
        $this->assertRoleGroupOfCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER, $owner);
    }

    public function testEditOwnerRoleGroupIsNotAllowed(): void
    {
        $owner = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL);

        $response = $this->editCustomerUser($owner, CustomerUserRoleGroupDataFixture::ROLE_GROUP_CATALOG_USER);

        $this->assertValidationErrorOfRoleGroup($response, CustomerUserRoleGroupChangeAllowed::CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED);
        $this->assertRoleGroupOfCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER, $owner);
    }

    public function testEditAccountantRoleGroupIsNotAllowed(): void
    {
        $accountant = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_ACCOUNTANT_EMAIL);

        $response = $this->editCustomerUser($accountant, CustomerUserRoleGroupDataFixture::ROLE_GROUP_CATALOG_USER);

        $this->assertValidationErrorOfRoleGroup($response, CustomerUserRoleGroupChangeAllowed::CUSTOMER_USER_ROLE_GROUP_CHANGE_NOT_ALLOWED);
        $this->assertRoleGroupOfCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_ACCOUNTANT, $accountant);
    }

    public function testEditUserRoleGroupToLimitedUserRoleGroup(): void
    {
        $customerUser = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_USER_EMAIL);

        $response = $this->editCustomerUser($customerUser, CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER);
        $responseData = $this->getResponseDataForGraphQlType($response, 'EditCustomerUserPersonalData');

        $this->assertSame($this->getRoleGroup(CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER)->getUuid(), $responseData['roleGroup']['uuid']);
        $this->assertRoleGroupOfCustomerUser(CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER, $customerUser);
    }

    public function testRemoveOwnerIsNotAllowed(): void
    {
        $this->doTestRemoveCustomerUserIsNotAllowed(CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL);
    }

    public function testRemoveAccountantIsNotAllowed(): void
    {
        $this->doTestRemoveCustomerUserIsNotAllowed(CompanyDataFixture::B2B_COMPANY_ACCOUNTANT_EMAIL);
    }

    public function testRemoveLimitedUser(): void
    {
        $limitedUser = $this->getCustomerUser(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL);

        $response = $this->removeCustomerUser($limitedUser);

        $this->assertTrue($response['data']['RemoveCustomerUser'] ?? null);
        $this->assertNull($this->customerUserFacade->findCustomerUserByEmailAndDomain(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL, $this->domain->getId()));
    }

    public function testCanRoleGroupBeChangedByCurrentCustomerUser(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CustomerUsersRoleGroupChangeabilityQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'customerUsers');

        $expectedRoleGroupChangeabilityByEmail = [
            CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL => false,
            CompanyDataFixture::B2B_COMPANY_USER_EMAIL => true,
            CompanyDataFixture::B2B_COMPANY_CUSTOMER_MANAGER_EMAIL => true,
            CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL => true,
            CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL => true,
            CompanyDataFixture::B2B_COMPANY_ACCOUNTANT_EMAIL => false,
        ];
        $actualRoleGroupChangeabilityByEmail = array_column($responseData, 'canRoleGroupBeChangedByCurrentCustomerUser', 'email');

        ksort($expectedRoleGroupChangeabilityByEmail);
        ksort($actualRoleGroupChangeabilityByEmail);

        $this->assertSame($expectedRoleGroupChangeabilityByEmail, $actualRoleGroupChangeabilityByEmail);
    }

    private function doTestRemoveCustomerUserIsNotAllowed(string $email): void
    {
        $customerUser = $this->getCustomerUser($email);

        $response = $this->removeCustomerUser($customerUser);

        $this->assertAccessDeniedError($response);
        $this->assertSame($customerUser->getId(), $this->customerUserFacade->getByUuid($customerUser->getUuid())->getId());
    }

    /**
     * @return array<string, mixed>
     */
    private function removeCustomerUser(CustomerUser $customerUser): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/RemoveCustomerUserMutation.graphql', [
            'customerUserUuid' => $customerUser->getUuid(),
        ]);
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
    private function editCustomerUser(
        CustomerUser $customerUser,
        string $roleGroupReferenceName,
        string $firstName = 'First name',
    ): array {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $customerUser->getUuid(),
            'firstName' => $firstName,
            'lastName' => 'Last name',
            'roleGroupUuid' => $this->getRoleGroup($roleGroupReferenceName)->getUuid(),
        ]);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function assertValidationErrorOfRoleGroup(array $response, string $expectedViolationCode): void
    {
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertSame($expectedViolationCode, $validationErrors['input.roleGroupUuid'][0]['code']);
    }

    private function assertRoleGroupOfCustomerUser(
        string $expectedRoleGroupReferenceName,
        CustomerUser $customerUser,
    ): void {
        $this->assertSame(
            $this->getRoleGroup($expectedRoleGroupReferenceName)->getUuid(),
            $this->customerUserFacade->getCustomerUserById($customerUser->getId())->getRoleGroup()->getUuid(),
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
