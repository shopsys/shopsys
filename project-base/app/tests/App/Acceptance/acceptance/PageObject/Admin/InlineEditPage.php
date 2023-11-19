<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Admin;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class InlineEditPage extends AbstractPage
{
    /**
     * @param int|null $rowId
     */
    public function startInlineEdit($rowId): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-inline-edit-edit';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->waitForAjax();
    }

    public function createNewRow(): void
    {
        $this->tester->clickByCss('.test-inline-edit-add');
        $this->tester->waitForAjax();
    }

    /**
     * @param int $rowId
     */
    public function delete($rowId): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .in-icon--delete';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->wait(1); // Pop-up animation

        $this->tester->clickByCss('.test-window-button-continue');
        $this->tester->waitForAjax();
    }

    /**
     * @param int|null $rowId
     * @param string $columnName
     * @param string $value
     */
    public function changeInputValue($rowId, $columnName, string $value): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-grid-column-' . $columnName . ' input';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->fillFieldByCss(
            $class,
            $value,
        );
    }

    /**
     * @param int|null $rowId
     */
    public function save($rowId): void
    {
        $class = $this->getRowCssLocator($rowId) . ' .test-inline-edit-save';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->waitForAjax();
    }

    /**
     * @return int|null
     */
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

    /**
     * @param int|null $rowId
     * @param string $columnName
     * @param string $text
     */
    public function assertSeeInColumn($rowId, $columnName, string $text): void
    {
        $this->tester->seeInCss($text, $this->getRowCssLocator($rowId) . ' .test-grid-column-' . $columnName);
    }

    /**
     * @param int $rowId
     * @param string $text
     */
    public function assertSeeInColumnPercent(int $rowId, string $text): void
    {
        $formattedPercent = $this->tester->getFormattedPercentAdmin($text);
        $this->assertSeeInColumn($rowId, 'percent', $formattedPercent);
    }

    /**
     * @param int $rowId
     */
    public function assertDontSeeRow($rowId): void
    {
        $this->tester->dontSeeElement(['css' => $this->getRowCssLocator($rowId)]);
    }

    /**
     * @param int|null $rowId
     * @return string
     */
    private function getRowCssLocator($rowId): string
    {
        if ($rowId === null) {
            return '.test-grid-row:not([data-inline-edit-row-id])';
        }

        return '.test-grid-row[data-inline-edit-row-id="' . $rowId . '"]';
    }
}
