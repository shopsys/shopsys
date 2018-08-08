<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin;

use Facebook\WebDriver\WebDriverBy;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class EntityEditPage extends AbstractPage
{
    public function uploadTestImage(string $imageUploadFieldId, string $testImageName): void
    {
        $imageUploadInput = $this->webDriver->findElement(WebDriverBy::id($imageUploadFieldId));

        $this->tester->attachFile($imageUploadInput, $testImageName);
        $this->tester->waitForAjax();
    }
}
