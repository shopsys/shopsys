<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Admin;

use Facebook\WebDriver\WebDriverBy;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class InlineEditPage extends AbstractPage
{
    public function startInlineEdit(?int $rowId): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-inline-edit-edit';
        $element = $this->webDriver->findElement(WebDriverBy::cssSelector($class));
        $this->tester->scrollToElement($element);
        $this->tester->clickByCss($class);
        $this->tester->waitForAjax();
    }

    public function createNewRow(): void
    {
        $this->tester->clickByCss('.test-inline-edit-add');
        $this->tester->waitForAjax();
    }

    public function delete(int $rowId): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-type-delete';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->wait(1); // Pop-up animation

        $this->tester->clickByCss('.test-window-button-continue');
        $this->tester->waitForAjax();
    }

    public function changeInputValue(?int $rowId, string $columnName, string $value): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-grid-column-' . $columnName . ' input';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->fillFieldByCss(
            $class,
            $value,
        );
    }

    public function save(?int $rowId): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-inline-edit-save';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->waitForAjax();
    }

    public function getHighestRowId(): ?int
    {
        $highestId = $this->webDriver->executeScript(
            'var highestId = null;
            $(".test-grid-row").each(function () {
                var $row = $(this);
                if ($row.data("inline-edit-row-id") > highestId) {
                    highestId = $row.data("inline-edit-row-id");
                }
            });
            return highestId;',
        );

        return is_numeric($highestId) ? (int)$highestId : null;
    }

    public function assertSeeInColumn(?int $rowId, string $columnName, string $text): void
    {
        $this->tester->seeInCss($text, $this->getRowCssLocator($rowId) . ' .test-grid-column-' . $columnName);
    }

    public function assertSeeInColumnPercent(int $rowId, string $text): void
    {
        $formattedPercent = $this->tester->getFormattedPercentAdmin($text);
        $this->assertSeeInColumn($rowId, 'percent', $formattedPercent);
    }

    public function assertDontSeeRow(int $rowId): void
    {
        $this->tester->dontSeeElement(['css' => $this->getRowCssLocator($rowId)]);
    }

    private function getRowCssLocator(?int $rowId): string
    {
        if ($rowId === null) {
            return '.test-grid-row:not([data-inline-edit-row-id])';
        }

        return '.test-grid-row[data-inline-edit-row-id="' . $rowId . '"]';
    }
}
