<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class FloatingWindowPage extends AbstractPage
{
    public function closeFloatingWindow(): void
    {
        $this->tester->clickByCss('.test-window-button-close');
        $this->tester->wait(1); // animation of closing sometime hides page content
    }
}
