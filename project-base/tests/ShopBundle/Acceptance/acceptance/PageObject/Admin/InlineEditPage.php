<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class InlineEditPage extends AbstractPage
{
    /**
     * @param int|null $rowId
     */
    public function startInlineEdit($rowId)
    {
        $class = $this->getRowCssLocator($rowId) . ' .js-inline-edit-edit';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->waitForAjax();
    }

    public function createNewRow()
    {
        $this->tester->clickByCss('.js-inline-edit-add');
        $this->tester->waitForAjax();
    }

    /**
     * @param int $rowId
     */
    public function delete($rowId)
    {
        $class = $this->getRowCssLocator($rowId) . ' .in-icon--delete';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->wait(1); // Pop-up animation

        $this->tester->clickByCss('.window-button-continue');
        $this->tester->waitForAjax();
    }

    /**
     * @param int|null $rowId
     * @param string $columnName
     * @param string $value
     */
    public function changeInputValue($rowId, $columnName, $value)
    {
        $class = $this->getRowCssLocator($rowId) . ' .js-grid-column-' . $columnName . ' input';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->fillFieldByCss(
            $class,
            $value
        );
    }

    /**
     * @param int|null $rowId
     */
    public function save($rowId)
    {
        $class = $this->getRowCssLocator($rowId) . ' .js-inline-edit-save';
        $this->tester->scrollTo(['css' => $class]);
        $this->tester->clickByCss($class);
        $this->tester->waitForAjax();
    }

    /**
     * @return int|null
     */
    public function getHighestRowId()
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

    /**
     * @param int|null $rowId
     * @param string $columnName
     * @param string $text
     */
    public function assertSeeInColumn($rowId, $columnName, $text)
    {
        $this->tester->seeInCss($text, $this->getRowCssLocator($rowId) . ' .js-grid-column-' . $columnName);
    }

    /**
     * @param int $rowId
     * @param string $text
     */
    public function assertSeeInColumnPercent(int $rowId, string $text)
    {
        $formattedPercent = $this->tester->getFormattedPercentAdmin($text);
        $this->assertSeeInColumn($rowId, 'percent', $formattedPercent);
    }

    /**
     * @param int $rowId
     */
    public function assertDontSeeRow($rowId)
    {
        $this->tester->dontSeeElement(['css' => $this->getRowCssLocator($rowId)]);
    }

    /**
     * @param int|null $rowId
     * @return string
     */
    private function getRowCssLocator($rowId)
    {
        if ($rowId === null) {
            return '.js-grid-row:not([data-inline-edit-row-id])';
        }

        return '.js-grid-row[data-inline-edit-row-id="' . $rowId . '"]';
    }
}
