<?php

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartBoxPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\FloatingWindowPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\HomepagePage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class CartCest
{
    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddingSameProductToCartMakesSum(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('have more pieces of the same product as one item in cart');
        $me->amOnPage('/brother-383dui/');

        $productDetailPage->addProductIntoCart(3);
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeInCartBox('1 item for CZK13,884.00');

        $productDetailPage->addProductIntoCart(3);
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeInCartBox('1 item for CZK27,768.00');

        $me->amOnPage('/cart/');

        $cartPage->assertProductQuantity('Brother 383dui', 6);
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddToCartFromProductListPage(
        CartPage $cartPage,
        ProductListPage $productListPage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add product to cart from product list');
        $me->amOnPage('/tv-audio/');
        $productListPage->addProductToCartByName('Microsoft 061qoh', 1);
        $me->see('Product Microsoft 061qoh (1 pcs) added to the cart');
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeInCartBox('1 item');
        $me->amOnPage('/cart/');
        $cartPage->assertProductPrice('Microsoft 061qoh', 'CZK10.00');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddToCartFromProductDetail(
        ProductDetailPage $productDetailPage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add product to cart from product detail');
        $me->amOnPage('/microsoft-061qoh/');
        $me->see('Add to cart');
        $productDetailPage->addProductIntoCart(3);
        $me->see('Product Microsoft 061qoh (3 pcs) added to the cart');
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeInCartBox('1 item for CZK30.00');
        $me->amOnPage('/cart/');
        $me->see('Microsoft 061qoh');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function testChangeCartItemAndRecalculatePrice(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('change items in cart and recalculate price');
        $me->amOnPage('/microsoft-061qoh/');
        $me->see('Add to cart');
        $productDetailPage->addProductIntoCart(3);
        $me->clickByText('Go to cart');

        $cartPage->changeProductQuantity('Microsoft 061qoh', 10);
        $cartPage->assertTotalPriceWithVat('CZK100.00');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function testRemovingItemsFromCart(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('add some items to cart and remove them');

        $me->amOnPage('/brother-383dui/');
        $productDetailPage->addProductIntoCart();
        $me->amOnPage('/gigabyte-947onk/');
        $productDetailPage->addProductIntoCart();

        $me->amOnPage('/cart/');
        $cartPage->assertProductIsInCartByName('Brother 383dui');
        $cartPage->assertProductIsInCartByName('Gigabyte 947onk');

        $cartPage->removeProductFromCart('Gigabyte 947onk');
        $cartPage->assertProductIsNotInCartByName('JGigabyte 947onk');

        $cartPage->removeProductFromCart('Brother 383dui');
        $me->see('Your cart is unfortunately empty.');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddingDistinctProductsToCart(
        CartPage $cartPage,
        CartBoxPage $cartBoxPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add distinct products to cart');

        $me->amOnPage('/brother-383dui/');
        $productDetailPage->addProductIntoCart();
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeInCartBox('1 item for CZK4,628.00');

        $me->amOnPage('/dlink-418qlz/');
        $productDetailPage->addProductIntoCart();
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeInCartBox('2 items for CZK4,633.00');

        $me->amOnPage('/cart/');
        $cartPage->assertProductIsInCartByName('Brother 383dui');
        $cartPage->assertProductIsInCartByName('Dlink 418qlz');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function testPricingInCart(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('see that prices of products in cart are calculated well');

        $me->amOnPage('/brother-383dui/');
        $productDetailPage->addProductIntoCart(10);
        $me->amOnPage('/dlink-418qlz/');
        $productDetailPage->addProductIntoCart(100);
        $me->amOnPage('/verbatim-173jqs/');
        $productDetailPage->addProductIntoCart(75);

        $me->amOnPage('/cart/');
        $cartPage->assertTotalPriceWithVat('CZK185,763,955.00');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function testPromoCodeFlowInCart(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('see that flow of promocode in cart is correct');

        $me->amOnPage('/brother-383dui/');
        $productDetailPage->addProductIntoCart();
        $me->amOnPage('/dlink-418qlz/');
        $productDetailPage->addProductIntoCart();

        $me->amOnPage('/cart/');

        $cartPage->applyPromoCode('test');

        $cartPage->canSeePromoCodeRemoveButtonElement();
        $cartPage->assertTotalPriceWithVat('CZK4,169.00');

        $cartPage->removePromoCode();

        $cartPage->canSeePromoCodeSubmitButtonElement();
        $cartPage->assertTotalPriceWithVat('CZK4,633.00');
    }
}
