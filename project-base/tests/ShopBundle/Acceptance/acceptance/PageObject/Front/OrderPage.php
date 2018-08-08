<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class OrderPage extends AbstractPage
{
    const FIRST_NAME_FIELD_NAME = 'order_personal_info_form[firstName]';
    
    public function assertTransportIsNotSelected(string $transportTitle): void
    {
        $this->tester->dontSeeCheckboxIsCheckedByLabel($transportTitle);
    }
    
    public function assertTransportIsSelected(string $transportTitle): void
    {
        $this->tester->seeCheckboxIsCheckedByLabel($transportTitle);
    }
    
    public function selectTransport(string $transportTitle): void
    {
        $this->tester->checkOptionByLabel($transportTitle);
    }
    
    public function assertPaymentIsNotSelected(string $paymentTitle): void
    {
        $this->tester->dontSeeCheckboxIsCheckedByLabel($paymentTitle);
    }
    
    public function assertPaymentIsSelected(string $paymentTitle): void
    {
        $this->tester->seeCheckboxIsCheckedByLabel($paymentTitle);
    }
    
    public function selectPayment(string $paymentTitle): void
    {
        $this->tester->checkOptionByLabel($paymentTitle);
    }
    
    public function fillFirstName(string $firstName): void
    {
        $this->tester->fillFieldByName(self::FIRST_NAME_FIELD_NAME, $firstName);
    }
    
    public function assertFirstNameIsFilled(string $firstName): void
    {
        $this->tester->seeInFieldByName($firstName, self::FIRST_NAME_FIELD_NAME);
    }
    
    public function fillPersonalInfo(string $firstName, string $lastName, string $email, string $telephone): void
    {
        $this->fillFirstName($firstName);
        $this->tester->fillFieldByName('order_personal_info_form[lastName]', $lastName);
        $this->tester->fillFieldByName('order_personal_info_form[email]', $email);
        $this->tester->fillFieldByName('order_personal_info_form[telephone]', $telephone);
    }
    
    public function fillBillingAddress(string $street, string $city, string $postcode): void
    {
        $this->tester->fillFieldByName('order_personal_info_form[street]', $street);
        $this->tester->fillFieldByName('order_personal_info_form[city]', $city);
        $this->tester->fillFieldByName('order_personal_info_form[postcode]', $postcode);
    }
    
    public function fillNote(string $note): void
    {
        $this->tester->fillFieldByName('order_personal_info_form[note]', $note);
    }

    public function acceptLegalConditions(): void
    {
        $this->tester->checkOptionByLabel('I agree with terms and conditions and privacy policy.');
    }
}
