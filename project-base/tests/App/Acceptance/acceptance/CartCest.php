<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Front\CartBoxPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\CartPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\FloatingWindowPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\HomepagePage;
use Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class CartCest
{
    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddingSameProductToCartMakesSum(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('have more pieces of the same product as one item in cart');
        // 22-sencor-sle-22f46dm4-hello-kitty
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 1]);
        $productDetailPage->addProductIntoCart(3);
        $floatingWindowPage->closeFloatingWindow();

        $cartBoxPage->seeCountAndPriceInCartBox(1, '10497');

        $productDetailPage->addProductIntoCart(3);
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeCountAndPriceInCartBox(1, '20994');

        $me->amOnLocalizedRoute('front_cart');

        $cartPage->assertProductQuantity('22" Sencor SLE 22F46DM4 HELLO KITTY', 6);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddToCartFromProductListPage(
        CartPage $cartPage,
        ProductListPage $productListPage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add product to cart from product list');
        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480', 1);
        $me->seeTranslationFrontend('Product <strong>%name%</strong> (%quantity% %unitName%) added to the cart', 'messages', [
            '%name%' => t('Defender 2.0 SPK-480', [], 'dataFixtures', $me->getFrontendLocale()),
            '%quantity%' => 1,
            '%unitName%' => $me->getDefaultUnitName(),
        ]);
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeCountAndPriceInCartBox(1, '119');
        $me->amOnLocalizedRoute('front_cart');
        $cartPage->assertProductPrice('Defender 2.0 SPK-480', '119');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\HomepagePage $homepagePage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddToCartFromHomepage(
        CartPage $cartPage,
        HomepagePage $homepagePage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add product to cart from homepage');
        $me->amOnPage('/');
        $homepagePage->addTopProductToCartByName('22" Sencor SLE 22F46DM4 HELLO KITTY', 1);
        $me->seeTranslationFrontend('Product <strong>%name%</strong> (%quantity% %unitName%) added to the cart', 'messages', [
            '%name%' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $me->getFrontendLocale()),
            '%quantity%' => 1,
            '%unitName%' => $me->getDefaultUnitName(),
        ]);
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeCountAndPriceInCartBox(1, '3499');
        $me->amOnLocalizedRoute('front_cart');
        $cartPage->assertProductPrice('22" Sencor SLE 22F46DM4 HELLO KITTY', '3499');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddToCartFromProductDetail(
        ProductDetailPage $productDetailPage,
        CartBoxPage $cartBoxPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add product to cart from product detail');
        // 22-sencor-sle-22f46dm4-hello-kitty
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 1]);
        $me->seeTranslationFrontend('Add to cart');
        $productDetailPage->addProductIntoCart(3);
        $me->seeTranslationFrontend('Product <strong>%name%</strong> (%quantity% %unitName%) added to the cart', 'messages', [
            '%name%' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $me->getFrontendLocale()),
            '%quantity%' => 3,
            '%unitName%' => $me->getDefaultUnitName(),
        ]);
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeCountAndPriceInCartBox(1, '10497');
        $me->amOnLocalizedRoute('front_cart');
        $me->seeTranslationFrontend('22" Sencor SLE 22F46DM4 HELLO KITTY', 'dataFixtures');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testChangeCartItemAndRecalculatePrice(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('change items in cart and recalculate price');

        // 22-sencor-sle-22f46dm4-hello-kitty
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 1]);
        $me->seeTranslationFrontend('Add to cart');
        $productDetailPage->addProductIntoCart(3);
        $me->clickByTranslationFrontend('Go to cart');

        $cartPage->changeProductQuantity('22" Sencor SLE 22F46DM4 HELLO KITTY', 10);

        $products = [
            '22" Sencor SLE 22F46DM4 HELLO KITTY' => 10,
        ];

        $cartPage->assertTotalPriceWithVatByProducts($products);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testRemovingItemsFromCart(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('add some items to cart and remove them');

        // panasonic-dmc-ft5ep
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 38]);
        $productDetailPage->addProductIntoCart();
        // jura-impressa-j9-tft-carbon
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 23]);
        $productDetailPage->addProductIntoCart();

        $me->amOnLocalizedRoute('front_cart');
        $cartPage->assertProductIsInCartByName('JURA Impressa J9 TFT Carbon');
        $cartPage->assertProductIsInCartByName('PANASONIC DMC FT5EP');

        $cartPage->removeProductFromCart('JURA Impressa J9 TFT Carbon');
        $cartPage->assertProductIsNotInCartByName('JURA Impressa J9 TFT Carbon');

        $cartPage->removeProductFromCart('PANASONIC DMC FT5EP');
        $me->seeTranslationFrontend('Your cart is unfortunately empty. To create order, you have to <a href="{{ url }}">choose</a> some product first', 'messages', [
            '{{ url }}' => '',
        ]);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartBoxPage $cartBoxPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\FloatingWindowPage $floatingWindowPage
     */
    public function testAddingDistinctProductsToCart(
        CartPage $cartPage,
        CartBoxPage $cartBoxPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me,
        FloatingWindowPage $floatingWindowPage
    ) {
        $me->wantTo('add distinct products to cart');

        // 22-sencor-sle-22f46dm4-hello-kitty
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 1]);
        $productDetailPage->addProductIntoCart();
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeCountAndPriceInCartBox(1, '3499');

        // canon-pixma-ip7250
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 142]);
        $productDetailPage->addProductIntoCart();
        $floatingWindowPage->closeFloatingWindow();
        $cartBoxPage->seeCountAndPriceInCartBox(2, '27687');

        $me->amOnLocalizedRoute('front_cart');
        $cartPage->assertProductIsInCartByName('22" Sencor SLE 22F46DM4 HELLO KITTY');
        $cartPage->assertProductIsInCartByName('Canon PIXMA iP7250');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testPricingInCart(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('see that prices of products in cart are calculated well');

        // aquila-aquagym-non-carbonated-spring-water
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 71]);
        $productDetailPage->addProductIntoCart(10);
        // 100-czech-crowns-ticket
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 72]);
        $productDetailPage->addProductIntoCart(100);
        // premiumcord-micro-usb-a-b-1m
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 73]);
        $productDetailPage->addProductIntoCart(75);

        $me->amOnLocalizedRoute('front_cart');

        $products = [
            'Aquila Aquagym non-carbonated spring water' => 10,
            '100 Czech crowns ticket' => 100,
            'PremiumCord micro USB, A-B, 1m' => 75,
        ];

        $cartPage->assertTotalPriceWithVatByProducts($products);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\CartPage $cartPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductDetailPage $productDetailPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testPromoCodeFlowInCart(
        CartPage $cartPage,
        ProductDetailPage $productDetailPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('see that flow of promocode in cart is correct');

        // aquila-aquagym-non-carbonated-spring-water
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 71]);
        $productDetailPage->addProductIntoCart();
        // 100-czech-crowns-ticket
        $me->amOnLocalizedRoute('front_product_detail', ['id' => 72]);
        $productDetailPage->addProductIntoCart();

        $me->amOnLocalizedRoute('front_cart');

        $products = [
            'Aquila Aquagym non-carbonated spring water' => 1,
            '100 Czech crowns ticket' => 1,
        ];

        $cartPage->applyPromoCode('test');

        $cartPage->canSeePromoCodeRemoveButtonElement();
        $cartPage->assertTotalPriceWithVatByProducts($products, 10);

        $cartPage->removePromoCode();

        $cartPage->canSeePromoCodeSubmitButtonElement();
        $cartPage->assertTotalPriceWithVatByProducts($products);
    }
}
