<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CustomerUserSelfManageTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_SELF_MANAGE_USER_EMAIL;

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testChangePersonalDataMutation()
     */
    public function testChangePersonalDataMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            ChangePersonalDataInputProvider::INPUT_ARRAY,
        );

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testCustomerUsersQuery()
     */
    public function testCustomerUsersQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CustomerUsersQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testRemoveCustomerUser()
     */
    public function testRemoveCustomerUserMutationIsNotAllowed(): void
    {
        $userToDelete = $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL, $this->domain->getId(), CustomerUser::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/RemoveCustomerUserMutation.graphql', [
            'customerUserUuid' => $userToDelete->getUuid(),
        ]);

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testEditAnotherCustomerUserPersonalData()
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testEditSelfCustomerUserPersonalData()
     */
    public function testEditAnotherCustomerUserPersonalDataIsNotAllowed(): void
    {
        $userToEdit = $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL, $this->domain->getId(), CustomerUser::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $userToEdit->getUuid(),
        ]);

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testEditAnotherCustomerUserPersonalDataIsNotAllowed()
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testEditAnotherCustomerUserPersonalData()
     */
    public function testEditSelfCustomerUserPersonalData(): void
    {
        $currentCustomerUser = $this->getCustomerUserByDefaultCredentials();
        $newRoleGroup = $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER, CustomerUserRoleGroup::class);

        $editedFirstName = 'Edited first name';
        $editedLastName = 'Edited last name';
        $editedTelephone = '001122456';
        $editedRoleGroupUuid = $newRoleGroup->getUuid();

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $currentCustomerUser->getUuid(),
            'firstName' => $editedFirstName,
            'lastName' => $editedLastName,
            'telephone' => $editedTelephone,
            'roleGroupUuid' => $editedRoleGroupUuid,
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'EditCustomerUserPersonalData');

        $this->assertSame($editedFirstName, $responseData['firstName']);
        $this->assertSame($editedLastName, $responseData['lastName']);
        $this->assertSame($editedTelephone, $responseData['telephone']);
        $this->assertSame($editedRoleGroupUuid, $responseData['roleGroup']['uuid']);

        $refreshedCurrentCustomerUser = $this->customerUserFacade->getCustomerUserById($currentCustomerUser->getId());
        $this->assertSame($editedFirstName, $refreshedCurrentCustomerUser->getFirstName());
        $this->assertSame($editedLastName, $refreshedCurrentCustomerUser->getLastName());
        $this->assertSame($editedTelephone, $refreshedCurrentCustomerUser->getTelephone());
        $this->assertSame($newRoleGroup->getUuid(), $refreshedCurrentCustomerUser->getRoleGroup()->getUuid());
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testAddNewCustomerUser()
     */
    public function testAddNewCustomerUserMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/AddNewCustomerUserMutation.graphql');

        $this->assertAccessDeniedError($response);
    }
}
