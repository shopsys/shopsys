<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use PHPUnit\Framework\Assert;
use Tests\App\Test\Codeception\AcceptanceTester;

class ErrorHandlingCest
{
    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testDisplayNotice(AcceptanceTester $me): void
    {
        $me->wantTo('display notice error page');
        $me->amOnPage('/codeception-acceptance-test/error-handler/notice');
        $me->seeTranslationFrontend('Oops! Error occurred.');
        $me->dontSee('Notice');
    }

    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function testAccessUnknownDomain(AcceptanceTester $me): void
    {
        $me->wantTo('display error when accessing an unknown domain');
        $me->amOnPage('/codeception-acceptance-test/error-handler/unknown-domain');
        $me->see('You are trying to access an unknown domain');
        $me->dontSeeTranslationFrontend('Page not found!');
    }

    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     */
    public function test500ErrorPage(AcceptanceTester $me): void
    {
        $me->wantTo('display 500 error and check error ID uniqueness');
        $me->amOnPage('/codeception-acceptance-test/error-handler/exception');
        $me->seeTranslationFrontend('Oops! Error occurred.');

        $cssIdentifier = ['css' => '#js-error-id'];
        $errorIdFirstAccess = $me->grabTextFrom($cssIdentifier);

        $me->amOnPage('/codeception-acceptance-test/error-handler/exception');
        $errorIdSecondAccess = $me->grabTextFrom($cssIdentifier);

        Assert::assertNotSame($errorIdFirstAccess, $errorIdSecondAccess);
    }
}
