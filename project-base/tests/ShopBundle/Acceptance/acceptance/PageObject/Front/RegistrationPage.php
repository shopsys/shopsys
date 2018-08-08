<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class RegistrationPage extends AbstractPage
{
    public function register(string $firstName, string $lastName, string $email, string $firstPassword, string $secondPassword): void
    {
        $this->tester->fillFieldByName('registration_form[firstName]', $firstName);
        $this->tester->fillFieldByName('registration_form[lastName]', $lastName);
        $this->tester->fillFieldByName('registration_form[email]', $email);
        $this->tester->fillFieldByName('registration_form[password][first]', $firstPassword);
        $this->tester->fillFieldByName('registration_form[password][second]', $secondPassword);
        $this->tester->checkOptionByLabel('I agree with privacy policy');
        $this->tester->wait(TimedFormTypeExtension::MINIMUM_FORM_FILLING_SECONDS);
        $this->tester->clickByName('registration_form[save]');
    }
    
    public function seeEmailError(string $text): void
    {
        $this->seeErrorForField('.js-validation-error-list-registration_form_email', $text);
    }
    
    public function seePasswordError(string $text): void
    {
        $this->seeErrorForField('.js-validation-error-list-registration_form_password_first', $text);
    }

    private function seeErrorForField($fieldClass, string $text): void
    {
        // Error message might be in popup - wait for animation
        $this->tester->wait(1);
        // Error message might be in fancy title - hover over field
        $this->tester->moveMouseOverByCss($fieldClass);

        $this->tester->see($text);
    }
}
