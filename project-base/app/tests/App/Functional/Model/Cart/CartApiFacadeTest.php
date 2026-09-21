<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class CartApiFacadeTest extends TransactionFunctionalTestCase
{
    private const string UNKNOWN_CART_UUID = '3c7d5f2a-9b41-4e6f-8a2d-1f0e5b6c7d89';

    /**
     * @inject
     */
    private CartApiFacade $cartApiFacade;

    public function testNewCartIsResolvedAfterTheMemoizedCartIsDeleted(): void
    {
        $cart = $this->cartApiFacade->getCartCreateIfNotExists(null, CartDataFixture::CART_UUID);
        $this->assertFalse($cart->isEmpty());

        $this->cartApiFacade->deleteCart($cart);
        $cartAfterDeletion = $this->cartApiFacade->getCartCreateIfNotExists(null, CartDataFixture::CART_UUID);

        $this->assertNotSame($cart, $cartAfterDeletion);
        $this->assertTrue($cartAfterDeletion->isEmpty());
    }

    public function testCartCreatedForUnknownUuidIsMemoizedOnceItIsPersisted(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $cart = $this->cartApiFacade->getCartCreateIfNotExists(null, self::UNKNOWN_CART_UUID);
        $this->cartApiFacade->addProductByUuidToCart($product->getUuid(), 1, false, $cart);

        $this->assertSame($cart, $this->cartApiFacade->getCartCreateIfNotExists(null, self::UNKNOWN_CART_UUID));
    }
}
