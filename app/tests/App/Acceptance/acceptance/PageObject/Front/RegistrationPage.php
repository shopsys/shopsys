<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class RegistrationPage extends AbstractPage
{
    private const MINIMUM_FORM_SUBMIT_WAIT_TIME = 10;

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $firstPassword
     * @param string $secondPassword
     * @param string $street
     * @param string $city
     * @param string $postcode
     * @param string $telephone
     */
    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $firstPassword,
        string $secondPassword,
        string $street,
        string $city,
        string $postcode,
        string $telephone
    ): void {
        $this->fillRegistrationForm(
            $firstName,
            $lastName,
            $email,
            $firstPassword,
            $secondPassword,
            $street,
            $city,
            $postcode,
            $telephone
        );

        $this->tester->wait(TimedFormTypeExtension::MINIMUM_FORM_FILLING_SECONDS);
        $this->tester->clickByName('registration_form[save]');
    }

    /**
     * @param string $text
     */
    public function seeEmailError($text)
    {
        $this->seeErrorForField('.js-validation-error-list-registration_form_email', $text);
    }

    /**
     * @param string $text
     */
    public function seePasswordError($text)
    {
        $this->seeErrorForField('.js-validation-error-list-registration_form_password_first', $text);
    }

    /**
     * @param string $fieldClass
     * @param string $text
     */
    private function seeErrorForField($fieldClass, $text)
    {
        // Error message might be in popup - wait for animation
        $this->tester->wait(1);
        // Error message might be in fancy title - hover over field
        $this->tester->moveMouseOverByCss($fieldClass);

        $this->tester->seeTranslationFrontend($text, 'validators');
    }

    public function checkRegistrationSuccessful(): void
    {
        $this->tester->wait(self::MINIMUM_FORM_SUBMIT_WAIT_TIME);
        $this->tester->seeTranslationFrontend('You have been successfully registered.');
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $firstPassword
     * @param string $secondPassword
     * @param string $street
     * @param string $city
     * @param string $postcode
     * @param string $telephone
     */
    public function fillRegistrationForm(
        string $firstName,
        string $lastName,
        string $email,
        string $firstPassword,
        string $secondPassword,
        string $street,
        string $city,
        string $postcode,
        string $telephone
    ): void {
        $this->tester->fillFieldByName('registration_form[firstName]', $firstName);
        $this->tester->fillFieldByName('registration_form[lastName]', $lastName);
        $this->tester->fillFieldByName('registration_form[email]', $email);
        $this->tester->fillFieldByName('registration_form[password][first]', $firstPassword);
        $this->tester->fillFieldByName('registration_form[password][second]', $secondPassword);
        $this->tester->fillFieldByName('registration_form[street]', $street);
        $this->tester->fillFieldByName('registration_form[city]', $city);
        $this->tester->fillFieldByName('registration_form[postcode]', $postcode);
        $this->tester->fillFieldByName('registration_form[telephone]', $telephone);

        $this->tester->clickWithLeftButton(['css' => 'label[for="registration_form_privacyPolicy"]'], 10, 10);
    }
}
