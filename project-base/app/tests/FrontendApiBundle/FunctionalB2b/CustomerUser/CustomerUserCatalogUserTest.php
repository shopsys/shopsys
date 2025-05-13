<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Tests\FrontendApiBundle\Functional\Order\MinimalOrderAsAuthenticatedCustomerUserTest;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalAndCompanyDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CustomerUserCatalogUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL;
    protected const string FAKE_UUID = '00000000-0000-0000-0000-000000000000';
    protected const string VALID_UUID = 'a5801a3e-48fe-40bb-a1af-f0719f40b632';

    public function testCartQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CartQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testPaymentsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/PaymentsQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testTransportsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Transport/graphql/TransportsQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testGoPaySwiftsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/GoPaySwiftsQuery.graphql', [
            'currencyCode' => 'CZK',
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderSentPageContentQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderPaymentSuccessfulContentQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderPaymentFailedContentQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderPaymentsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/OrderPaymentsQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testPaymentQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/PaymentQuery.graphql', [
            'uuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testTransportQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Transport/graphql/TransportQuery.graphql', [
            'uuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrdersQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/getOrders.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/GetOrderQuery.graphql', [
            'uuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderItemsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/GetOrderItemsQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderItemsSearchQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/SearchOrderItemsQuery.graphql', [
            'searchInput' => [
                'search' => 'whatever',
                'userIdentifier' => self::FAKE_UUID,
            ],
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testLastOrderQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/LastOrderQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testComplaintQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Complaint/graphql/GetComplaintQuery.graphql', [
            'complaintNumber' => 'complaintNumber',
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testComplaintsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Complaint/graphql/GetComplaintsQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testCreateOrderMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/CreateMinimalOrderMutation.graphql', [
            ...MinimalOrderAsAuthenticatedCustomerUserTest::DEFAULT_INPUT_VALUES,
            'isDeliveryAddressDifferentFromBilling' => false,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testPayOrderMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/PayOrderMutation.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testUpdatePaymentStatusMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/UpdatePaymentStatusMutation.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testChangePaymentInOrderMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Payment/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => self::FAKE_UUID,
                'paymentUuid' => self::FAKE_UUID,
            ],
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testAddToCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => self::FAKE_UUID,
            'quantity' => 1,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testRemoveFromCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Cart/graphql/RemoveFromCart.graphql', [
            'cartItemUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testAddOrderItemsToCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Cart/graphql/AddOrderItemsToCart.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testChangePaymentInCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => self::FAKE_UUID,
            'paymentUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testChangeTransportInCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => self::FAKE_UUID,
            'transportUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testApplyPromoCodeToCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/_graphql/mutation/ApplyPromoCodeToCart.graphql', [
            'cartUuid' => self::FAKE_UUID,
            'promoCode' => 'promoCode',
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testRemovePromoCodeFromCartMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Cart/graphql/RemovePromoCodeFromCart.graphql', [
            'cartUuid' => self::FAKE_UUID,
            'promoCode' => 'promoCode',
        ]);

        $this->assertAccessDeniedError($response);
    }

    public function testCreateComplaintMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Complaint/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => self::VALID_UUID,
                    'resolution' => ComplaintResolutionEnum::FIX,
                    'email' => 'email',
                    'items' => [
                        [
                            'quantity' => 1,
                            'description' => 'Broken!!!',
                            'orderItemUuid' => self::FAKE_UUID,
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
        );

        $this->assertAccessDeniedError($response);
    }

    public function testChangePersonalDataMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/ChangePersonalDataMutation.graphql',
            ChangePersonalAndCompanyDataInputProvider::PERSONAL_DATA_INPUT_ARRAY,
        );

        $this->assertAccessDeniedError($response);
    }

    public function testDeleteDeliveryAddressMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/_graphql/mutation/DeleteDeliveryAddressMutation.graphql',
            [
                'uuid' => self::FAKE_UUID,
            ],
        );

        $this->assertAccessDeniedError($response);
    }

    public function testEditDeliveryAddressMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/_graphql/mutation/EditDeliveryAddressMutation.graphql',
            [
                'uuid' => self::FAKE_UUID,
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'street' => 'street',
                'city' => 'city',
                'postcode' => '12345',
                'country' => 'CZ',
                'companyName' => 'Shopsys',
                'telephone' => '777777777',
            ],
        );

        $this->assertAccessDeniedError($response);
    }

    public function testSetDefaultDeliveryAddressMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/_graphql/mutation/SetDefaultDeliveryAddressMutation.graphql',
            [
                'deliveryAddressUuid' => self::FAKE_UUID,
            ],
        );

        $this->assertAccessDeniedError($response);
    }

    public function testCreateDeliveryAddressMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/_graphql/mutation/CreateDeliveryAddressMutation.graphql',
            [
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'street' => 'street',
                'city' => 'city',
                'postcode' => '12345',
                'country' => 'CZ',
                'companyName' => 'Shopsys',
                'telephone' => '777777777',
            ],
        );

        $this->assertAccessDeniedError($response);
    }
}
