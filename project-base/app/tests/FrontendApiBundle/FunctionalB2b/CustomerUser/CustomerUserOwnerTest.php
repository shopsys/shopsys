<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyComplaintDataFixture;
use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\CompanyOrderDataFixture;
use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrontendApiBundle\Component\Constraints\UniqueBillingAddressApi;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalAndCompanyDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CustomerUserOwnerTest extends GraphQlB2bDomainWithLoginTestCase
{
    private const string COMPLAINT_EMAIL = 'no-reply@shopsys.com';

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

        $input = ChangePersonalAndCompanyDataInputProvider::INPUT_ARRAY;
        $input['companyNumber'] = $existingBillingAddress->getCompanyNumber();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalAndCompanyDataMutation.graphql',
            $input,
        );
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $validations = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(UniqueBillingAddressApi::DUPLICATE_BILLING_ADDRESS, $validations['input'][0]['code']);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testChangeCompanyDataMutationIsNotAllowed()
     */
    public function testChangePersonalAndCompanyDataMutation(): void
    {
        $personalData = ChangePersonalAndCompanyDataInputProvider::INPUT_ARRAY;
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalAndCompanyDataMutation.graphql',
            $personalData,
        );
        $responseData = [
            ...$this->getResponseDataForGraphQlType($response, 'ChangePersonalData'),
            ...$this->getResponseDataForGraphQlType($response, 'ChangeCompanyData'),
        ];

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
            ['email' => CompanyDataFixture::B2B_COMPANY_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_ACCOUNTANT_EMAIL],
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

    public function testCompanyOrdersList(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrdersQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'orders');

        $expectedOrders = [
            ['uuid' => $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 31, Order::class)->getUuid(), 'email' => CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL],
            ['uuid' => $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 29, Order::class)->getUuid(), 'email' => CompanyDataFixture::B2B_COMPANY_USER_EMAIL],
            ['uuid' => $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 30, Order::class)->getUuid(), 'email' => CompanyDataFixture::B2B_COMPANY_USER_EMAIL],
            ['uuid' => $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 28, Order::class)->getUuid(), 'email' => CompanyDataFixture::B2B_COMPANY_USER_EMAIL],
            ['uuid' => $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 26, Order::class)->getUuid(), 'email' => CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL],
            ['uuid' => $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 27, Order::class)->getUuid(), 'email' => CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL],
        ];

        $this->assertSame(6, $responseData['totalCount']);

        foreach ($responseData['edges'] as $key => $orderDataEdge) {
            $this->assertArrayHasKey('node', $orderDataEdge);
            $orderData = $orderDataEdge['node'];
            $this->assertSame($expectedOrders[$key], $orderData);
        }
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testGetAnotherCustomerUserOrderDetailQueryReturnsNotFound()
     */
    public function testGetAnotherCustomerUserOrderDetail(): void
    {
        $anotherUserOrder = $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 28, Order::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrderQuery.graphql', [
            'orderUuid' => $anotherUserOrder->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'order');

        $this->assertSame($anotherUserOrder->getUuid(), $responseData['uuid']);
        $this->assertSame($anotherUserOrder->getEmail(), $responseData['email']);
        $this->assertSame($anotherUserOrder->getEmail(), $responseData['customerUser']['email']);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testGetAnotherUserComplaintReturnsNotFound()
     */
    public function testGetAnotherUserComplaint(): void
    {
        $complaint = $this->getReferenceForDomain(CompanyComplaintDataFixture::COMPANY_USER_COMPLAINT, $this->domain->getId(), Complaint::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/ComplaintQuery.graphql', [
            'number' => $complaint->getNumber(),
        ]);

        $this->assertSame($complaint->getUuid(), $this->getResponseDataForGraphQlType($response, 'complaint')['uuid']);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testGetComplaintsReturnsOwnComplaintsOnly()
     */
    public function testGetComplaintsReturnsAllCompanyComplaints(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/ComplaintsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'complaints');

        $this->assertSame(2, $responseData['totalCount']);

        $expectedComplaintsData = [
            ['node' => ['uuid' => $this->getReferenceForDomain(CompanyComplaintDataFixture::COMPANY_USER_COMPLAINT, $this->domain->getId(), Complaint::class)->getUuid()]],
            ['node' => ['uuid' => $this->getReferenceForDomain(CompanyComplaintDataFixture::COMPANY_OWNER_COMPLAINT, $this->domain->getId(), Complaint::class)->getUuid()]],
        ];
        $this->assertSame(
            $expectedComplaintsData,
            $responseData['edges'],
        );
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testCreateComplaintForAnotherUserOrderIsNotAllowed()
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testCreateComplaintIsAllowedForOwnOrder()
     */
    public function testCreateComplaintMutationIsAllowedForAnotherUserOrder(): void
    {
        $anotherUserOrder = $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 28, Order::class);
        $orderItems = $anotherUserOrder->getItems();
        $orderItem = reset($orderItems);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Complaint/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $anotherUserOrder->getUuid(),
                    'resolution' => ComplaintResolutionEnum::FIX,
                    'email' => 'no-reply@shopsys.com',
                    'items' => [
                        [
                            'quantity' => 1,
                            'description' => 'Broken!!!',
                            'orderItemUuid' => $orderItem->getUuid(),
                            'files' => [null],
                        ],
                    ],
                    'deliveryAddress' => [
                        'firstName' => 'firstName',
                        'lastName' => 'lastnName',
                        'street' => 'street 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => '+420123456789',
                        'country' => 'CZ',
                    ],
                ],
            ],
            [
                1 => __DIR__ . '/../../Functional/Complaint/files/1.jpg',
            ],
            [
                1 => ['variables.input.items.0.files.0'],
            ],
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertArrayHasKey('email', $responseData);
        $this->assertSame(self::COMPLAINT_EMAIL, $responseData['email']);
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
