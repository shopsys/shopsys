<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

abstract class AbstractPage
{
    protected RemoteWebDriver $webDriver;

    protected AcceptanceTester $tester;

    /**
     * @param \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver
     * @param \Tests\App\Test\Codeception\AcceptanceTester $tester
     */
    public function __construct(StrictWebDriver $strictWebDriver, AcceptanceTester $tester)
    {
        $this->webDriver = $strictWebDriver->webDriver;
        $this->tester = $tester;
    }
}
