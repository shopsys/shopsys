<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CustomerUserWithLimitedRoleGroupTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    private CustomerUserDataFactory $customerUserDataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logout();

        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1, CustomerUser::class);
        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->roleGroup = $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_LIMITED_USER, CustomerUserRoleGroup::class);

        $this->customerUserFacade->editCustomerUser($customerUser->getId(), $customerUserData);

        $this->login();
    }

    public function testCustomerUserCannotSeeProductPrices(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/ProductQuery.graphql', [
            'uuid' => $product->getUuid(),
        ]);

        $productData = $this->getResponseDataForGraphQlType($response, 'product');
        $price = $productData['price'];
        $this->assertSame('***', $price['priceWithVat']);
        $this->assertSame('***', $price['priceWithoutVat']);
    }

    public function testCustomerUserCannotSeeOrderPrices(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '4', Order::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/OrderDetailQuery.graphql', [
            'uuid' => $order->getUuid(),
        ]);

        $orderData = $this->getResponseDataForGraphQlType($response, 'order');

        $this->assertSame('***', $orderData['totalPrice']['priceWithVat']);
        $this->assertSame('***', $orderData['totalPrice']['priceWithoutVat']);

        foreach ($orderData['items'] as $item) {
            $this->assertSame('***', $item['totalPrice']['priceWithVat']);
            $this->assertSame('***', $item['totalPrice']['priceWithoutVat']);
            $this->assertSame('***', $item['unitPrice']['priceWithVat']);
            $this->assertSame('***', $item['unitPrice']['priceWithoutVat']);
        }
    }

    public function testCustomerUserCannotSeeGatewayPayments(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/PaymentsQuery.graphql');

        $payments = $this->getResponseDataForGraphQlType($response, 'payments');
        $this->assertCount(4, $payments);

        foreach ($payments as $payment) {
            $this->assertSame('***', $payment['price']['priceWithVat']);
            $this->assertSame('***', $payment['price']['priceWithoutVat']);
            $this->assertSame(Payment::TYPE_BASIC, $payment['type']);
        }
    }

    public function testCustomerUserCannotUseFreeTransport(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 100,
        ]);

        $addToCart = $this->getResponseDataForGraphQlType($response, 'AddToCart');

        $newlyCreatedCart = $addToCart['cart'];

        self::assertNull(
            $newlyCreatedCart['remainingAmountWithVatForFreeTransport'],
            'Actual remaining price has to be null for limited user who cannot see prices',
        );
    }

    public function testCustomerUserCannotUseFilterByPrice(): void
    {
        $minimalPrice = $this->getFormattedMoneyAmountConvertedToDomainDefaultCurrency('75000');
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/ProductsQuery.graphql', [
            'first' => 1,
            'minimalPrice' => $minimalPrice,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('Filtering by price is not allowed for current user.', $errors[0]['message']);
    }

    public function testCustomerUserCannotUseOrderingByPrice(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/ProductsQuery.graphql', [
            'first' => 1,
            'orderingMode' => 'PRICE_ASC',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('Ordering by price is not allowed for current user.', $errors[0]['message']);
    }
}
