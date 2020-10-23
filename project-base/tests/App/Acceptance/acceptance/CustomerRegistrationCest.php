<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class CustomerRegistrationCest
{
    protected const MINIMUM_FORM_SUBMIT_WAIT_TIME = 10;

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testSuccessfulRegistration(
        RegistrationPage $registrationPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage
    ) {
        $me->wantTo('successfully register new customer');
        $me->amOnPage('/');
        $layoutPage->clickOnRegistration();

        $me->reloadPage();

        $registrationPage->register('Roman', 'Štěpánek', 'no-reply.16@shopsys.com', 'user123', 'user123');
        $me->wait(self::MINIMUM_FORM_SUBMIT_WAIT_TIME);
        $me->seeTranslationFrontend('You have been successfully registered.');
        $me->see('Roman Štěpánek');
        $me->seeTranslationFrontend('Log out');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testAlreadyUsedEmail(RegistrationPage $registrationPage, AcceptanceTester $me)
    {
        $me->wantTo('use already used email while registration');
        $me->amOnLocalizedRoute('front_registration_register');
        $registrationPage->register('Roman', 'Štěpánek', 'no-reply@shopsys.com', 'user123', 'user123');
        $registrationPage->seeEmailError('This email is already registered');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testPasswordMismatch(RegistrationPage $registrationPage, AcceptanceTester $me)
    {
        $me->wantTo('use mismatching passwords while registration');
        $me->amOnLocalizedRoute('front_registration_register');
        $registrationPage->register(
            'Roman',
            'Štěpánek',
            'no-reply.16@shopsys.com',
            'user123',
            'missmatchingPassword'
        );
        $registrationPage->seePasswordError('Passwords do not match');
    }
}
