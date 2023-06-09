<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Shopsys\FrameworkBundle\Component\Form\TimedFormTypeExtension;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Module\StrictWebDriver;
use Tests\FrameworkBundle\Test\Codeception\FrontCheckbox;

class RegistrationPage extends AbstractPage
{
    private const MINIMUM_FORM_SUBMIT_WAIT_TIME = 10;

    /**
     * @param \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver
     * @param \Tests\App\Test\Codeception\AcceptanceTester $tester
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     */
    public function __construct(StrictWebDriver $strictWebDriver, AcceptanceTester $tester, private readonly LoginPage $loginPage)
    {
        parent::__construct($strictWebDriver, $tester);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $firstPassword
     * @param string $secondPassword
     */
    public function register($firstName, $lastName, $email, $firstPassword, $secondPassword)
    {
        $this->tester->fillFieldByName('registration_form[firstName]', $firstName);
        $this->tester->fillFieldByName('registration_form[lastName]', $lastName);
        $this->tester->fillFieldByName('registration_form[email]', $email);
        $this->tester->fillFieldByName('registration_form[password][first]', $firstPassword);
        $this->tester->fillFieldByName('registration_form[password][second]', $secondPassword);

        $frontCheckboxClicker = FrontCheckbox::createByCss(
            $this->tester,
            '#registration_form_privacyPolicy',
        );
        $frontCheckboxClicker->check();

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

        $this->tester->seeTranslationFrontend($text, Translator::VALIDATOR_TRANSLATION_DOMAIN);
    }

    /**
     * @param string|null $fullName
     */
    public function checkRegistrationSuccessful(?string $fullName = null): void
    {
        $this->tester->wait(self::MINIMUM_FORM_SUBMIT_WAIT_TIME);
        $this->tester->seeTranslationFrontend('You have been successfully registered.');
        $this->loginPage->checkUserLogged($fullName);
    }
}
