<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class InlineEditPage extends AbstractPage
{
    public function startInlineEdit(?int $rowId): void
    {
        $this->tester->clickByCss($this->getRowCssLocator($rowId) . ' .js-inline-edit-edit');
        $this->tester->waitForAjax();
    }

    public function createNewRow(): void
    {
        $this->tester->clickByCss('.js-inline-edit-add');
        $this->tester->waitForAjax();
    }
    
    public function delete(int $rowId): void
    {
        $this->tester->clickByCss($this->getRowCssLocator($rowId) . ' .in-icon--delete');
        $this->tester->wait(1); // Pop-up animation

        $this->tester->clickByCss('.window-button-continue');
        $this->tester->waitForAjax();
    }

    public function changeInputValue(?int $rowId, string $columnName, string $value): void
    {
        $this->tester->fillFieldByCss(
            $this->getRowCssLocator($rowId) . ' .js-grid-column-' . $columnName . ' input',
            $value
        );
    }

    public function save(?int $rowId): void
    {
        $this->tester->clickByCss($this->getRowCssLocator($rowId) . ' .js-inline-edit-save');
        $this->tester->waitForAjax();
    }

    public function getHighestRowId(): ?int
    {
        $highestId = $this->webDriver->executeScript(
            'var highestId = null;
            $(".js-grid-row").each(function () {
                var $row = $(this);
                if ($row.data("inline-edit-row-id") > highestId) {
                    highestId = $row.data("inline-edit-row-id");
                }
            });
            return highestId;'
        );

        return is_numeric($highestId) ? (int)$highestId : null;
    }

    public function assertSeeInColumn(?int $rowId, string $columnName, string $text): void
    {
        $this->tester->seeInCss($text, $this->getRowCssLocator($rowId) . ' .js-grid-column-' . $columnName);
    }
    
    public function assertDontSeeRow(int $rowId): void
    {
        $this->tester->dontSeeElement(['css' => $this->getRowCssLocator($rowId)]);
    }

    private function getRowCssLocator(?int $rowId): string
    {
        if ($rowId === null) {
            return '.js-grid-row:not([data-inline-edit-row-id])';
        }

        return '.js-grid-row[data-inline-edit-row-id="' . $rowId . '"]';
    }
}
