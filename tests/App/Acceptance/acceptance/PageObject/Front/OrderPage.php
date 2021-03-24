<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class OrderPage extends AbstractPage
{
    private const FIRST_NAME_FIELD_NAME = 'order_personal_info_form[firstName]';

    /**
     * @param string $transportTitle
     */
    public function assertTransportIsNotSelected($transportTitle)
    {
        $translatedTransportTitle = t($transportTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->dontSeeCheckboxIsCheckedByLabel($translatedTransportTitle);
    }

    /**
     * @param string $transportTitle
     */
    public function assertTransportIsSelected($transportTitle)
    {
        $translatedTransportTitle = t($transportTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->seeCheckboxIsCheckedByLabel($translatedTransportTitle);
    }

    /**
     * @param int $transportPosition
     */
    public function selectTransport($transportPosition)
    {
        $this->tester->clickByCss('label[for=transport_and_payment_form_transport_' . $transportPosition . ']');
        $this->tester->waitForAjax();
    }

    /**
     * @param string $paymentTitle
     */
    public function assertPaymentIsNotSelected($paymentTitle)
    {
        $this->scrollToPaymentForm();
        $translatedPaymentTitle = t($paymentTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->dontSeeCheckboxIsCheckedByLabel($translatedPaymentTitle);
    }

    /**
     * @param string $paymentTitle
     */
    public function assertPaymentIsSelected($paymentTitle)
    {
        $this->scrollToPaymentForm();
        $translatedPaymentTitle = t($paymentTitle, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $this->tester->seeCheckboxIsCheckedByLabel($translatedPaymentTitle);
    }

    /**
     * @param int $paymentPosition
     */
    public function selectPayment($paymentPosition)
    {
        $this->tester->clickByCss('label[for=transport_and_payment_form_payment_' . $paymentPosition . ']');
        $this->tester->waitForAjax();
    }

    /**
     * @param string $firstName
     */
    public function fillFirstName($firstName)
    {
        $this->selectCommonCustomer();
        $this->tester->fillFieldByName(self::FIRST_NAME_FIELD_NAME, $firstName);
    }

    /**
     * @param string $email
     */
    public function fillEmail(string $email): void
    {
        $this->tester->fillFieldByName('order_personal_info_form[email]', $email);
        $this->tester->waitForAjax();
        $this->tester->wait(1);
    }

    /**
     * @param string $firstName
     */
    public function assertFirstNameIsFilled($firstName)
    {
        $this->tester->seeInFieldByName($firstName, self::FIRST_NAME_FIELD_NAME);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $telephone
     */
    public function fillPersonalInfo($firstName, $lastName, $email, $telephone)
    {
        $this->fillEmail($email);
        $this->selectCommonCustomer();
        $this->fillFirstName($firstName);
        $this->tester->fillFieldByName('order_personal_info_form[lastName]', $lastName);
        $this->tester->fillFieldByName('order_personal_info_form[telephone]', $telephone);
    }

    /**
     * @param string $street
     * @param string $city
     * @param string $postcode
     */
    public function fillBillingAddress($street, $city, $postcode)
    {
        $this->tester->fillFieldByName('order_personal_info_form[street]', $street);
        $this->tester->fillFieldByName('order_personal_info_form[city]', $city);
        $this->tester->fillFieldByName('order_personal_info_form[postcode]', $postcode);

        $this->tester->clickByName('order_personal_info_form[city]');

        $this->tester->waitForAjax();
        $this->tester->wait(1);
    }

    /**
     * @param string $note
     */
    public function fillNote($note)
    {
        $this->tester->fillFieldByName('order_personal_info_form[note]', $note);
    }

    private function scrollToPaymentForm()
    {
        $this->tester->scrollTo(['css' => '#transport_and_payment_form_payment']);
    }

    public function clickGoToCartInPopUpWindow(): void
    {
        $this->tester->clickByTranslationFrontend(
            CartPage::GO_TO_CART_TRANSLATION_CONSTANT,
            'messages',
            [],
            WebDriverBy::cssSelector('#window-main-container')
        );
    }

    public function continueToSecondStep(): void
    {
        $this->tester->clickByTranslationFrontend('Doprava a platba');
    }

    public function continueToThirdStep(): void
    {
        $this->tester->clickByTranslationFrontend('Vaše údaje');
    }

    public function goBackToSecondStep(): void
    {
        $this->tester->clickByTranslationFrontend('Zpět na Dopravu a platbu');
    }

    public function finishOrder(): void
    {
        $this->tester->clickByTranslationFrontend('Odeslat objednávku');
    }

    public function checkOrderFinishedSuccessfully(): void
    {
        $this->tester->seeTranslationFrontend('Děkujeme za vaši objednávku');
    }

    public function selectCommonCustomer(): void
    {
        $this->tester->clickByCss('.js-tabs-button[data-tab-id="common-customer"]');
    }
}
