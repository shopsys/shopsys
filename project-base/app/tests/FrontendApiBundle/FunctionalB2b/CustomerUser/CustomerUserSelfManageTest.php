<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyComplaintDataFixture;
use App\DataFixtures\Demo\CompanyDataFixture;
use App\DataFixtures\Demo\CompanyOrderDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalAndCompanyDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CustomerUserSelfManageTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_USER_EMAIL;
    protected const string COMPLAINT_EMAIL = 'no-reply@shopsys.com';

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testChangePersonalAndCompanyDataMutation()
     */
    public function testChangeCompanyDataMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/ChangeCompanyDataMutation.graphql',
            ChangePersonalAndCompanyDataInputProvider::COMPANY_DATA_INPUT_ARRAY,
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
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testEditSelfCustomerUserPersonalDataIsNotAllowed()
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
    public function testEditSelfCustomerUserPersonalDataIsNotAllowed(): void
    {
        $currentCustomerUser = $this->getCustomerUserByDefaultCredentials();

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/EditCustomerUserPersonalDataMutation.graphql', [
            'customerUserUuid' => $currentCustomerUser->getUuid(),
        ]);

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testAddNewCustomerUser()
     */
    public function testAddNewCustomerUserMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/AddNewCustomerUserMutation.graphql');

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testGetAnotherCustomerUserOrderDetail()
     */
    public function testGetAnotherCustomerUserOrderDetailQueryReturnsNotFound(): void
    {
        $anotherUserOrder = $this->getReferenceForDomain(CompanyOrderDataFixture::COMPANY_ORDER_PREFIX . 1, $this->domain->getId(), Order::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrderQuery.graphql', [
            'orderUuid' => $anotherUserOrder->getUuid(),
        ]);

        $this->assertUserError($response, 'order-not-found', 404);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testGetAnotherUserComplaint()
     */
    public function testGetAnotherUserComplaintReturnsNotFound(): void
    {
        $complaint = $this->getReferenceForDomain(CompanyComplaintDataFixture::COMPANY_OWNER_COMPLAINT, $this->domain->getId(), Complaint::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/ComplaintQuery.graphql', [
            'number' => $complaint->getNumber(),
        ]);

        $this->assertUserError($response, 'complaint-not-found', 404);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testGetComplaintsReturnsAllCompanyComplaints()
     */
    public function testGetComplaintsReturnsOwnComplaintsOnly(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/ComplaintsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'complaints');

        $this->assertSame(1, $responseData['totalCount']);

        $expectedComplaintsData = [
            ['node' => ['uuid' => $this->getReferenceForDomain(CompanyComplaintDataFixture::COMPANY_USER_COMPLAINT, $this->domain->getId(), Complaint::class)->getUuid()]],
        ];
        $this->assertSame(
            $expectedComplaintsData,
            $responseData['edges'],
        );
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testCreateComplaintMutationIsAllowedForAnotherUserOrder()
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testCreateComplaintIsAllowedForOwnOrder()
     */
    public function testCreateComplaintForAnotherUserOrderIsNotAllowed(): void
    {
        $anotherUserOrder = $this->getReferenceForDomain(CompanyOrderDataFixture::COMPANY_ORDER_PREFIX . 1, $this->domain->getId(), Order::class);
        $response = $this->getCreateComplaintResponse($anotherUserOrder);

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserOwnerTest::testCreateComplaintMutationIsAllowedForAnotherUserOrder()
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\CustomerUserSelfManageTest::testCreateComplaintForAnotherUserOrderIsNotAllowed()
     */
    public function testCreateComplaintIsAllowedForOwnOrder(): void
    {
        $thisUserOrder = $this->getReferenceForDomain(CompanyOrderDataFixture::COMPANY_ORDER_PREFIX . 3, $this->domain->getId(), Order::class);
        $response = $this->getCreateComplaintResponse($thisUserOrder);

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertArrayHasKey('email', $responseData);
        $this->assertSame(self::COMPLAINT_EMAIL, $responseData['email']);
    }

    private function getCreateComplaintResponse(Order $order): array
    {
        $orderItems = $order->getItems();
        $orderItem = reset($orderItems);

        return $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Complaint/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'resolution' => ComplaintResolutionEnum::FIX,
                    'email' => self::COMPLAINT_EMAIL,
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
    }
}
