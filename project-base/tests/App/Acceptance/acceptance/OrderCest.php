<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;
use Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Helper\SymfonyHelper;

class OrderCest
{
    private const TRANSPORT_CZECH_POST_POSITION = 0;
    private const PAYMENT_CACHE_ON_DELIVERY = 1;

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testFormRemembersPaymentAndTransportWhenClickingBack(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('have my payment and transport remembered by order');

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $me->waitForAjax();
        $orderPage->clickGoToCartInPopUpWindow();
        $me->clickByTranslationFrontend('Order [verb]');

        $orderPage->assertTransportIsNotSelected('Czech post');
        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $me->waitForAjax();
        $orderPage->assertPaymentIsNotSelected('Cash on delivery');
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $me->waitForAjax();
        $me->clickByTranslationFrontend('Continue in order');
        $me->clickByTranslationFrontend('Back to shipping and payment selection');

        $orderPage->assertTransportIsSelected('Czech post');
        $orderPage->assertPaymentIsSelected('Cash on delivery');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testFormRemembersPaymentAndTransportWhenGoingDirectlyToUrl(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me
    ) {
        $me->wantTo('have my payment and transport remembered by order');

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $orderPage->clickGoToCartInPopUpWindow();
        $me->clickByTranslationFrontend('Order [verb]');

        $orderPage->assertTransportIsNotSelected('Czech post');
        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $me->waitForAjax();
        $orderPage->assertPaymentIsNotSelected('Cash on delivery');
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $me->waitForAjax();
        $me->clickByTranslationFrontend('Continue in order');
        $me->amOnLocalizedRoute('front_order_index');

        $orderPage->assertTransportIsSelected('Czech post');
        $orderPage->assertPaymentIsSelected('Cash on delivery');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testFormRemembersFirstName(ProductListPage $productListPage, OrderPage $orderPage, AcceptanceTester $me)
    {
        $me->wantTo('have my first name remembered by order');

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $orderPage->clickGoToCartInPopUpWindow();
        $me->clickByTranslationFrontend('Order [verb]');
        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $me->waitForAjax();
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $me->waitForAjax();
        $me->clickByTranslationFrontend('Continue in order');

        $orderPage->fillFirstName('Jan');
        $me->clickByTranslationFrontend('Back to shipping and payment selection');
        $me->amOnLocalizedRoute('front_order_index');
        $me->clickByTranslationFrontend('Continue in order');

        $orderPage->assertFirstNameIsFilled('Jan');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper
     */
    public function testOrderCanBeCompletedAndHasGoogleAnalyticsTrackingIdInSource(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me,
        SymfonyHelper $symfonyHelper
    ) {
        $scriptFacade = $symfonyHelper->grabServiceFromContainer(ScriptFacade::class);
        $this->setGoogleAnalyticsTrackingId('GA-test', $scriptFacade);

        $this->testOrderCanBeCompleted($productListPage, $orderPage, $me);

        $me->seeInSource('GA-test');
    }

    /**
     * @param string $trackingId
     * @param \Shopsys\FrameworkBundle\Model\Script\ScriptFacade $scriptFacade
     */
    private function setGoogleAnalyticsTrackingId($trackingId, ScriptFacade $scriptFacade)
    {
        $scriptFacade->setGoogleAnalyticsTrackingId($trackingId, Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    private function testOrderCanBeCompleted(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me
    ) {
        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName('Defender 2.0 SPK-480');
        $orderPage->clickGoToCartInPopUpWindow();
        $me->clickByTranslationFrontend('Order [verb]');

        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $me->waitForAjax();
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $me->waitForAjax();
        $me->clickByTranslationFrontend('Continue in order');

        $orderPage->fillPersonalInfo('Karel', 'Novák', 'no-reply@shopsys.com', '123456789');
        $orderPage->fillBillingAddress('Koksární 10', 'Ostrava', '702 00');
        $me->waitForAjax();
        $orderPage->acceptLegalConditions();

        $me->clickByTranslationFrontend('Finish the order');

        $me->seeTranslationFrontend('Order sent');
    }
}
