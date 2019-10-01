<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance;

use PHPUnit\Framework\Assert;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class ErrorHandlingCest
{
    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function testDisplayNotice(AcceptanceTester $me)
    {
        $me->wantTo('display notice error page');
        $me->amOnPage('/test/error-handler/notice');
        $me->seeTranslationFrontend('Oops! Error occurred.');
        $me->dontSee('Notice');
    }

    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function testAccessUnknownDomain(AcceptanceTester $me)
    {
        $me->wantTo('display error when accessing an unknown domain');
        $me->amOnPage('/test/error-handler/unknown-domain');
        $me->see('You are trying to access an unknown domain');
        $me->dontSeeTranslationFrontend('Page not found!');
    }

    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     */
    public function test500ErrorPage(AcceptanceTester $me)
    {
        $me->wantTo('display 500 error and check error ID uniqueness');
        $me->amOnPage('/test/error-handler/exception');
        $me->seeTranslationFrontend('Oops! Error occurred');

        $cssIdentifier = ['css' => '#js-error-id'];
        $errorIdFirstAccess = $me->grabTextFrom($cssIdentifier);

        $me->amOnPage('/test/error-handler/exception');
        $errorIdSecondAccess = $me->grabTextFrom($cssIdentifier);

        Assert::assertNotSame($errorIdFirstAccess, $errorIdSecondAccess);
    }
}
