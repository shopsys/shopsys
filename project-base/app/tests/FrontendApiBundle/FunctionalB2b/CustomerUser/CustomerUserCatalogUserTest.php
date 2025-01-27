<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use Tests\FrontendApiBundle\Functional\Order\MinimalOrderAsAuthenticatedCustomerUserTest;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CustomerUserCatalogUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_CATALOG_USER_EMAIL;
    protected const string FAKE_UUID = '00000000-0000-0000-0000-000000000000';

    public function testCartQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CartQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrdersQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrdersQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrderQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderItemsQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrderItemsQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderItemsSearchQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/OrderItemsSearchQuery.graphql', [
            'search' => 'search',
            'userIdentifier' => self::FAKE_UUID,
        ]);

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

    public function testLastOrderQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/LastOrderQuery.graphql');

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
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/PaymentSuccessfulPageContentQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

        $this->assertAccessDeniedWarning($response);
    }

    public function testOrderPaymentFailedContentQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/Order/graphql/PaymentFailedPageContentQuery.graphql', [
            'orderUuid' => self::FAKE_UUID,
        ]);

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
}
