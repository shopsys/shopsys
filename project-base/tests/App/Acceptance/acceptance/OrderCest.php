<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Script\ScriptFacade;
use Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Helper\SymfonyHelper;

class OrderCest
{
    private const TRANSPORT_CZECH_POST_POSITION = 0;
    private const PAYMENT_CACHE_ON_DELIVERY = 1;

    private const DEFAULT_TRANSPORT_NAME = 'Czech post';
    private const DEFAULT_PAYMENT_NAME = 'Cash on delivery';
    private const DEFAULT_PRODUCT_NAME = 'Defender 2.0 SPK-480';

    private const DEFAULT_BILLING_STREET = 'Koksární 10';
    private const DEFAULT_BILLING_CITY = 'Ostrava';
    private const DEFAULT_BILLING_POSTCODE = '702 00';
    private const DEFAULT_PHONE = '123456789';

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testFormRemembersPaymentAndTransportWhenClickingBack(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me,
    ) {
        $me->wantTo('have my payment and transport remembered by order');

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName(self::DEFAULT_PRODUCT_NAME);

        $orderPage->clickGoToCartInPopUpWindow();

        $orderPage->continueToSecondStep();

        $orderPage->assertTransportIsNotSelected(self::DEFAULT_TRANSPORT_NAME);
        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $orderPage->assertPaymentIsNotSelected(self::DEFAULT_PAYMENT_NAME);
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);

        $orderPage->continueToThirdStep();

        $orderPage->goBackToSecondStep();

        $orderPage->assertTransportIsSelected(self::DEFAULT_TRANSPORT_NAME);
        $orderPage->assertPaymentIsSelected(self::DEFAULT_PAYMENT_NAME);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testFormRemembersPaymentAndTransportWhenGoingDirectlyToUrl(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me,
    ) {
        $me->wantTo('have my payment and transport remembered by order');

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName(self::DEFAULT_PRODUCT_NAME);
        $orderPage->clickGoToCartInPopUpWindow();
        $orderPage->continueToSecondStep();

        $orderPage->assertTransportIsNotSelected(self::DEFAULT_TRANSPORT_NAME);
        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $orderPage->assertPaymentIsNotSelected(self::DEFAULT_PAYMENT_NAME);
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $orderPage->continueToThirdStep();

        $me->amOnLocalizedRoute('front_order_index');
        $orderPage->assertTransportIsSelected(self::DEFAULT_TRANSPORT_NAME);
        $orderPage->assertPaymentIsSelected(self::DEFAULT_PAYMENT_NAME);
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testFormRemembersFirstName(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me,
    ) {
        $me->wantTo('have my first name remembered by order');

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName(self::DEFAULT_PRODUCT_NAME);
        $orderPage->clickGoToCartInPopUpWindow();
        $orderPage->continueToSecondStep();

        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $orderPage->continueToThirdStep();

        $orderPage->fillFirstName('Jan');
        $orderPage->goBackToSecondStep();

        $me->amOnLocalizedRoute('front_order_index');
        $orderPage->continueToThirdStep();

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
        SymfonyHelper $symfonyHelper,
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
        AcceptanceTester $me,
    ) {
        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->addProductToCartByName(self::DEFAULT_PRODUCT_NAME);
        $orderPage->clickGoToCartInPopUpWindow();
        $orderPage->continueToSecondStep();

        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);
        $orderPage->continueToThirdStep();

        $orderPage->fillPersonalInfo('Karel', 'Novák', 'no-reply@shopsys.com', self::DEFAULT_PHONE);
        $orderPage->fillBillingAddress(
            self::DEFAULT_BILLING_STREET,
            self::DEFAULT_BILLING_CITY,
            self::DEFAULT_BILLING_POSTCODE,
        );
        $orderPage->acceptLegalConditions();

        $orderPage->finishOrder();

        $orderPage->checkOrderFinishedSuccessfully();
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\OrderPage $orderPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testOrderCanBeCompletedAsLoggedCustomer(
        ProductListPage $productListPage,
        OrderPage $orderPage,
        AcceptanceTester $me,
        RegistrationPage $registrationPage,
        LayoutPage $layoutPage,
    ) {
        $me->wantTo('Send order as logged customer');

        $me->amOnPage('/');
        $layoutPage->clickOnRegistration();
        $registrationPage->register(
            CustomerRegistrationCest::DEFAULT_USER_FIRST_NAME,
            CustomerRegistrationCest::DEFAULT_USER_LAST_NAME,
            CustomerRegistrationCest::DEFAULT_USER_EMAIL,
            CustomerRegistrationCest::DEFAULT_USER_PASSWORD,
            CustomerRegistrationCest::DEFAULT_USER_PASSWORD,
        );
        $registrationPage->checkRegistrationSuccessful(
            CustomerRegistrationCest::DEFAULT_USER_FIRST_NAME . ' ' . CustomerRegistrationCest::DEFAULT_USER_LAST_NAME,
        );

        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);

        $productListPage->addProductToCartByName(self::DEFAULT_PRODUCT_NAME);
        $orderPage->clickGoToCartInPopUpWindow();
        $orderPage->continueToSecondStep();

        $orderPage->selectTransport(self::TRANSPORT_CZECH_POST_POSITION);
        $orderPage->selectPayment(self::PAYMENT_CACHE_ON_DELIVERY);

        $orderPage->continueToThirdStep();

        $orderPage->fillPersonalInfo(
            CustomerRegistrationCest::DEFAULT_USER_FIRST_NAME,
            CustomerRegistrationCest::DEFAULT_USER_LAST_NAME,
            CustomerRegistrationCest::DEFAULT_USER_EMAIL,
            self::DEFAULT_PHONE,
        );
        $orderPage->fillBillingAddress(
            self::DEFAULT_BILLING_STREET,
            self::DEFAULT_BILLING_CITY,
            self::DEFAULT_BILLING_POSTCODE,
        );
        $orderPage->acceptLegalConditions();

        $orderPage->finishOrder();

        $orderPage->checkOrderFinishedSuccessfully();
    }
}
