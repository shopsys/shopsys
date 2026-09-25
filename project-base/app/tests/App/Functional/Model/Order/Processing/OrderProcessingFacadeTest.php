<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Processing;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Order\OrderFacade;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Tests\App\Test\TransactionFunctionalTestCase;

final class OrderProcessingFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testUnchangedCartSharesProcessedOrderDataWithinRequest(): void
    {
        $cart = $this->createCartWithProduct(ProductDataFixture::PRODUCT_PREFIX . '1');
        $domainConfig = $this->domain->getCurrentDomainConfig();

        $firstOrderData = $this->orderFacade->createOrderDataFromCart($cart, $domainConfig);
        $secondOrderData = $this->orderFacade->createOrderDataFromCart($cart, $domainConfig);

        $this->assertSame($firstOrderData, $secondOrderData);
    }

    public function testChangedCartIsProcessedAgain(): void
    {
        $cart = $this->createCartWithProduct(ProductDataFixture::PRODUCT_PREFIX . '1');
        $domainConfig = $this->domain->getCurrentDomainConfig();
        $orderDataBeforeChange = $this->orderFacade->createOrderDataFromCart($cart, $domainConfig);

        $anotherProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class);
        $this->cartFacade->addProductToExistingCart($anotherProduct, 1, $cart);
        $orderDataAfterChange = $this->orderFacade->createOrderDataFromCart($cart, $domainConfig);

        $this->assertNotSame($orderDataBeforeChange, $orderDataAfterChange);
        $this->assertCount(1, $orderDataBeforeChange->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT));
        $this->assertCount(2, $orderDataAfterChange->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT));
    }

    private function createCartWithProduct(string $productReferenceName): Cart
    {
        $cart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists(
            new CustomerUserIdentifier('order-processing-facade-test'),
        );
        $product = $this->getReference($productReferenceName, Product::class);
        $this->cartFacade->addProductToExistingCart($product, 1, $cart);

        return $cart;
    }
}
