<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\InlineEditPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class VatInlineEditCest
{
    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\InlineEditPage $inlineEditPage
     */
    public function testVatEdit(AcceptanceTester $me, LoginPage $loginPage, InlineEditPage $inlineEditPage)
    {
        $me->wantTo('vat can be edited via inline edit');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/vat/list');

        $inlineEditPage->startInlineEdit(1);
        $inlineEditPage->changeInputValue(1, 'name', 'test edited');
        $inlineEditPage->save(1);

        $inlineEditPage->assertSeeInColumn(1, 'name', 'test edited');
    }

    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\InlineEditPage $inlineEditPage
     */
    public function testVatDeleteAndCreate(AcceptanceTester $me, LoginPage $loginPage, InlineEditPage $inlineEditPage)
    {
        $me->wantTo('vat can be created and deleted via inline edit');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/vat/list');

        $inlineEditPage->createNewRow();
        $inlineEditPage->changeInputValue(null, 'name', 'test created');
        $inlineEditPage->changeInputValue(null, 'percent', '5');
        $inlineEditPage->save(null);

        $newRowId = $inlineEditPage->getHighestRowId();

        $inlineEditPage->assertSeeInColumn($newRowId, 'name', 'test created');
        $inlineEditPage->assertSeeInColumnPercent($newRowId, '5');

        $inlineEditPage->delete($newRowId);

        $inlineEditPage->assertDontSeeRow($newRowId);
        $me->seeTranslationAdmin('VAT <strong>%name%</strong> deleted', 'messages', [
            '%name%' => 'test created',
        ]);
    }
}
