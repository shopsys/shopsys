<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrontendApiBundle\Component\Constraints\UniqueBillingAddressApi;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CustomerUserOwnerTest extends GraphQlB2bDomainWithLoginTestCase
{
    /**
     * @inject
     */
    private CustomerUserDataFactory $customerUserDataFactory;

    /**
     * @see \Tests\FrontendApiBundle\Functional\Customer\User\CurrentCustomerUserTest::testUniqueBillingAddressIsNotValidatedInEditCustomerCompanyB2c()
     */
    public function testUniqueBillingAddressIsValidatedInEditCustomerCompany(): void
    {
        $existingBillingAddress = $this->getReferenceForDomain(CompanyDataFixture::SHOPSYS_COMPANY, $this->domain->getId(), Customer::class)->getBillingAddress();

        $input = ChangePersonalDataInputProvider::INPUT_ARRAY;
        $input['companyNumber'] = $existingBillingAddress->getCompanyNumber();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            $input,
        );
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $validations = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(UniqueBillingAddressApi::DUPLICATE_BILLING_ADDRESS, $validations['input'][0]['code']);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testChangePersonalDataMutationIsNotAllowed()
     */
    public function testChangePersonalDataMutation(): void
    {
        $personalData = ChangePersonalDataInputProvider::INPUT_ARRAY;
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            $personalData,
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePersonalData');

        $this->assertSame($personalData['telephone'], $responseData['telephone']);
        $this->assertSame($personalData['firstName'], $responseData['firstName']);
        $this->assertSame($personalData['lastName'], $responseData['lastName']);
        $this->assertSame($personalData['newsletterSubscription'], $responseData['newsletterSubscription']);
        $this->assertSame($personalData['street'], $responseData['street']);
        $this->assertSame($personalData['country'], $responseData['country']['code']);
        $this->assertSame($personalData['postcode'], $responseData['postcode']);
        $this->assertSame($personalData['companyName'], $responseData['companyName']);
        $this->assertSame($personalData['companyNumber'], $responseData['companyNumber']);
        $this->assertSame($personalData['companyTaxNumber'], $responseData['companyTaxNumber']);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testCustomerUsersQueryIsNotAllowed()
     */
    public function testCustomerUsersQuery(): void
    {
        $expectedData = [
            ['email' => CompanyDataFixture::B2B_COMPANY_SELF_MANAGE_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CustomerUsersQuery.graphql');

        $responseData = $this->getResponseDataForGraphQlType($response, 'customerUsers');

        $this->assertSame($expectedData, $responseData);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testRemoveCustomerUserMutationIsNotAllowed()
     */
    public function testRemoveCustomerUser(): void
    {
        $userToDelete = $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL, $this->domain->getId(), CustomerUser::class);

        $this->doTestSuccessfulCustomerUserRemoval($userToDelete);
    }

    public function testRemoveAnotherOwner(): void
    {
        $ownerToDelete = $this->getAnotherOwnerToDelete();

        $this->doTestSuccessfulCustomerUserRemoval($ownerToDelete);
    }

    public function testRemoveSelfIsNotAllowed(): void
    {
        $currentCustomerUser = $this->getReferenceForDomain(static::DEFAULT_USER_EMAIL, $this->domain->getId(), CustomerUser::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/RemoveCustomerUserMutation.graphql', [
            'customerUserUuid' => $currentCustomerUser->getUuid(),
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('cannot-remove-own-customer-user', $errors[0]['extensions']['userCode']);
    }

    public function testRemoveUserFromAnotherCompanyIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/RemoveCustomerUserMutation.graphql', [
            'customerUserUuid' => $this->getCustomerUserFromAnotherCompany()->getUuid(),
        ]);

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testEditAnotherCustomerUserPersonalDataIsNotAllowed()
     */
    public function testEditAnotherCustomerUserPersonalData(): void
    {
        $userToEdit = $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL, $this->domain->getId(), CustomerUser::class);
        $newRoleGroup = $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER, CustomerUserRoleGroup::class);

        $editedFirstName = 'Edited first name';
        $editedLastName = 'Edited last name';
        $editedTelephone = '001122456';
        $editedRoleGroupUuid = $newRoleGroup->getUuid();

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $userToEdit->getUuid(),
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

        $refreshedUserToEdit = $this->customerUserFacade->getCustomerUserById($userToEdit->getId());
        $this->assertSame($editedFirstName, $refreshedUserToEdit->getFirstName());
        $this->assertSame($editedLastName, $refreshedUserToEdit->getLastName());
        $this->assertSame($editedTelephone, $refreshedUserToEdit->getTelephone());
        $this->assertSame($newRoleGroup->getUuid(), $refreshedUserToEdit->getRoleGroup()->getUuid());
    }

    public function testEditCustomerUserPersonalDataFromAnotherCompanyIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $this->getCustomerUserFromAnotherCompany()->getUuid(),
        ]);

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testAddNewCustomerUserMutationIsNotAllowed()
     */
    public function testAddNewCustomerUser(): void
    {
        $currentCustomerUser = $this->getCustomerUserByDefaultCredentials();

        $firstName = 'First name';
        $lastName = 'Last name';
        $email = 'no-reply1111@shopsys.com';
        $telephone = '123456789';
        $roleGroupUuid = $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER, CustomerUserRoleGroup::class)->getUuid();

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/AddNewCustomerUserMutation.graphql', [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'telephone' => $telephone,
            'roleGroupUuid' => $roleGroupUuid,
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'AddNewCustomerUser');

        $this->assertSame($firstName, $responseData['firstName']);
        $this->assertSame($lastName, $responseData['lastName']);
        $this->assertSame($email, $responseData['email']);
        $this->assertSame($telephone, $responseData['telephone']);
        $this->assertSame($roleGroupUuid, $responseData['roleGroup']['uuid']);
        $this->assertSame($currentCustomerUser->getCustomer()->getBillingAddress()->getUuid(), $responseData['billingAddressUuid']);
    }

    /**
     * @return \App\Model\Customer\User\CustomerUser
     */
    private function getAnotherOwnerToDelete(): CustomerUser
    {
        $userToDelete = $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL, $this->domain->getId(), CustomerUser::class);
        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($userToDelete);
        $customerUserData->roleGroup = $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_OWNER, CustomerUserRoleGroup::class);
        $userToDelete->edit($customerUserData);
        $this->em->flush();

        return $userToDelete;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $userToDelete
     */
    private function doTestSuccessfulCustomerUserRemoval(CustomerUser $userToDelete): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/RemoveCustomerUserMutation.graphql', [
            'customerUserUuid' => $userToDelete->getUuid(),
        ]);

        $this->assertTrue($response['data']['RemoveCustomerUser'] ?? null);

        $this->expectException(CustomerUserNotFoundException::class);
        $this->customerUserFacade->getByUuid($userToDelete->getUuid());
    }

    /**
     * @return \App\Model\Customer\User\CustomerUser
     */
    private function getCustomerUserFromAnotherCompany(): CustomerUser
    {
        return $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '13', CustomerUser::class);
    }
}
