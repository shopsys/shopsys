<?php

namespace Tests\ShopBundle\Test\Codeception\Helper;

use Codeception\Module;
use Codeception\Util\Uri;
use Tests\ShopBundle\Test\Codeception\Module\StrictWebDriver;

class WebDriverHelper extends Module
{
    private function getWebDriver(): \Tests\ShopBundle\Test\Codeception\Module\StrictWebDriver
    {
        return $this->getModule(StrictWebDriver::class);
    }

    public function seeCurrentPageEquals(string $page): void
    {
        $expectedUrl = Uri::appendPath($this->getWebDriver()->_getUrl(), $page);
        $currentUrl = $this->getWebDriver()->webDriver->getCurrentURL();

        $this->assertSame($expectedUrl, $currentUrl);
    }
}
