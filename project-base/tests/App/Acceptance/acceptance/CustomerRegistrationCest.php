<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class CustomerRegistrationCest
{
    public const DEFAULT_USER_FIRST_NAME = 'Roman';
    public const DEFAULT_USER_LAST_NAME = 'Štěpánek';
    public const DEFAULT_USER_PASSWORD = 'user123';
    public const DEFAULT_USER_EMAIL = 'no-reply.16@shopsys.com';

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testSuccessfulRegistration(
        RegistrationPage $registrationPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage
    ): void {
        $me->wantTo('successfully register new customer');
        $me->amOnPage('/');
        $layoutPage->clickOnRegistration();

        $me->reloadPage();

        $registrationPage->register(
            self::DEFAULT_USER_FIRST_NAME,
            self::DEFAULT_USER_LAST_NAME,
            self::DEFAULT_USER_EMAIL,
            self::DEFAULT_USER_PASSWORD,
            self::DEFAULT_USER_PASSWORD
        );

        $registrationPage->checkRegistrationSuccessful(
            self::DEFAULT_USER_FIRST_NAME . ' ' . self::DEFAULT_USER_LAST_NAME
        );
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testAlreadyUsedEmail(RegistrationPage $registrationPage, AcceptanceTester $me): void
    {
        $me->wantTo('use already used email while registration');
        $me->amOnLocalizedRoute('front_registration_register');
        $registrationPage->register(
            self::DEFAULT_USER_FIRST_NAME,
            self::DEFAULT_USER_LAST_NAME,
            'no-reply@shopsys.com',
            self::DEFAULT_USER_PASSWORD,
            self::DEFAULT_USER_PASSWORD
        );
        $registrationPage->seeEmailError('This email is already registered');
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\RegistrationPage $registrationPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testPasswordMismatch(RegistrationPage $registrationPage, AcceptanceTester $me): void
    {
        $me->wantTo('use mismatching passwords while registration');
        $me->amOnLocalizedRoute('front_registration_register');
        $registrationPage->register(
            self::DEFAULT_USER_FIRST_NAME,
            self::DEFAULT_USER_LAST_NAME,
            self::DEFAULT_USER_EMAIL,
            self::DEFAULT_USER_PASSWORD,
            'missmatchingPassword'
        );
        $registrationPage->seePasswordError('Passwords do not match');
    }
}
