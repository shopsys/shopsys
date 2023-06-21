<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Admin;

use Facebook\WebDriver\WebDriverBy;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class EntityEditPage extends AbstractPage
{
    /**
     * @param string $imageUploadFieldId
     * @param string $testImageName
     */
    public function uploadTestImage($imageUploadFieldId, $testImageName)
    {
        $imageUploadInput = $this->webDriver->findElement(WebDriverBy::id($imageUploadFieldId));

        $this->tester->attachFile($imageUploadInput, $testImageName);
        $this->tester->waitForAjax();
    }
}
