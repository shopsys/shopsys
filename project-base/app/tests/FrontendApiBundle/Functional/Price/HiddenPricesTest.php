<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Price;

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

class HiddenPricesTest extends GraphQlWithLoginTestCase
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
        $query = '
            query {
                product(uuid: "' . $product->getUuid() . '") {
                    price {
                        priceWithVat
                        priceWithoutVat
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $price = $response['data']['product']['price'];
        $this->assertSame('***', $price['priceWithVat']);
        $this->assertSame('***', $price['priceWithoutVat']);
    }

    public function testCustomerUserCannotSeeOrderPrices(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '4', Order::class);
        $query = '
            query {
                order(uuid: "' . $order->getUuid() . '") {
                    totalPrice {
                        priceWithVat
                        priceWithoutVat
                    }
                    items {
                        totalPrice {
                            priceWithVat
                            priceWithoutVat
                        }
                        unitPrice {
                            priceWithVat
                            priceWithoutVat
                        }
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $order = $response['data']['order'];
        $this->assertSame('***', $order['totalPrice']['priceWithVat']);
        $this->assertSame('***', $order['totalPrice']['priceWithoutVat']);

        foreach ($order['items'] as $item) {
            $this->assertSame('***', $item['totalPrice']['priceWithVat']);
            $this->assertSame('***', $item['totalPrice']['priceWithoutVat']);
            $this->assertSame('***', $item['unitPrice']['priceWithVat']);
            $this->assertSame('***', $item['unitPrice']['priceWithoutVat']);
        }
    }

    public function testCustomerUserCannotSeeGatewayPayments(): void
    {
        $query = '
            query {
                payments {
                    uuid
                    type
                    price {
                        priceWithVat
                        priceWithoutVat
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $payments = $response['data']['payments'];
        $this->assertCount(4, $payments);

        foreach ($payments as $payment) {
            $this->assertSame('***', $payment['price']['priceWithVat']);
            $this->assertSame('***', $payment['price']['priceWithoutVat']);
            $this->assertSame(Payment::TYPE_BASIC, $payment['type']);
        }
    }
}
