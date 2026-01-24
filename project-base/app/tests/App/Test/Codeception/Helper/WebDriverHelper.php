<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Helper;

use Codeception\Module;
use Codeception\Util\Uri;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

class WebDriverHelper extends Module
{
    private function getWebDriver(): StrictWebDriver
    {
        /** @var \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver */
        $strictWebDriver = $this->getModule(StrictWebDriver::class);

        return $strictWebDriver;
    }

    public function seeCurrentPageEquals(string $page): void
    {
        $expectedUrl = Uri::appendPath($this->getWebDriver()->_getUrl(), $page);
        $currentUrl = $this->getWebDriver()->webDriver->getCurrentURL();

        $this->assertSame($expectedUrl, $currentUrl);
    }
}
