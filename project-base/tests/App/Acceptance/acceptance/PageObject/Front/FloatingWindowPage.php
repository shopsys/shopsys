<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class FloatingWindowPage extends AbstractPage
{
    public function closeFloatingWindow()
    {
        $this->tester->clickByCss('.test-window-button-close');
        // animation of closing sometime hides page content
        $this->tester->wait(1);
    }
}
