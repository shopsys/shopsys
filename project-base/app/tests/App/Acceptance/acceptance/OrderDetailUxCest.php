<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;
use Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\App\Test\Codeception\AcceptanceTester;

final class OrderDetailUxCest
{
    public function testOrderDetailInteractions(AcceptanceTester $me, LoginPage $loginPage): void
    {
        $me->wantTo('use order detail tabs and actions without duplicated modals or layout jumps');
        $loginPage->loginAsAdmin();
        $this->openFirstOrderDetail($me);
        $me->seeElement(
            WebDriverBy::cssSelector(
                'a.text-primary[data-live-section-param][data-bs-toggle="tooltip"][aria-label]',
            ),
        );
        $this->assertTabSwitchKeepsScrollPosition($me);
        $this->assertDoubleClickOpensSingleProductPicker($me);
    }

    private function openFirstOrderDetail(AcceptanceTester $me): void
    {
        $me->amOnPage('/admin/order/list');
        $me->waitForElement('tbody tr:first-child a[href*="/admin/order/edit/"]');
        $orderDetailPath = (string)$me->executeJS(<<<'JS'
            const url = new URL(document.querySelector('tbody tr:first-child a[href*="/admin/order/edit/"]').href);

            return url.pathname + url.search;
            JS);
        $me->amOnPage($orderDetailPath);
        $me->waitForElement('#nav-history');
    }

    private function assertTabSwitchKeepsScrollPosition(AcceptanceTester $me): void
    {
        $tabSwitchState = (array)$me->executeJS(<<<'JS'
            const historyTab = document.querySelector('#nav-history');
            const activePane = document.querySelector('.js-order-detail-tab-pane.active');
            const scrollingElement = document.scrollingElement;
            activePane.style.minHeight = `${scrollingElement.scrollHeight + scrollingElement.clientHeight}px`;
            void activePane.offsetHeight;
            window.scrollTo({
                top: scrollingElement.scrollHeight - scrollingElement.clientHeight,
                behavior: 'instant',
            });
            const scrollPositionBeforeSwitch = window.scrollY;

            historyTab.click();

            return {
                scrollPositionBeforeSwitch,
                scrollPositionDuringLoading: window.scrollY,
            };
            JS);

        Assert::assertSame(
            (int)$tabSwitchState['scrollPositionBeforeSwitch'],
            (int)$tabSwitchState['scrollPositionDuringLoading'],
            json_encode($tabSwitchState, JSON_THROW_ON_ERROR),
        );

        $me->waitForJS("return document.querySelector('#tab-history > .text-secondary') === null;", 10);
        $scrollPositionAfterLoading = (int)$me->executeAsyncJS(<<<'JS'
            requestAnimationFrame(() => requestAnimationFrame(() => arguments[0](window.scrollY)));
            JS);
        Assert::assertEqualsWithDelta(
            (int)$tabSwitchState['scrollPositionBeforeSwitch'],
            $scrollPositionAfterLoading,
            1,
        );
    }

    private function assertDoubleClickOpensSingleProductPicker(AcceptanceTester $me): void
    {
        $me->executeJS("document.querySelector('#nav-items').click();");
        $me->waitForElementVisible('.js-order-items-add-product');
        $initialModalCount = (int)$me->executeJS("return document.querySelectorAll('.modal').length;");

        $me->executeJS(<<<'JS'
            const addProductButton = [...document.querySelectorAll('.js-order-items-add-product')]
                .find(button => button.offsetParent !== null);
            addProductButton.click();
            addProductButton.click();
            JS);
        $me->waitForElementVisible('.modal.show');
        $modalState = (array)$me->executeJS(<<<'JS'
            return {
                openModalCount: document.querySelectorAll('.modal.show').length,
                totalModalCount: document.querySelectorAll('.modal').length,
            };
            JS);

        Assert::assertSame(1, (int)$modalState['openModalCount'], json_encode($modalState, JSON_THROW_ON_ERROR));
        Assert::assertSame(
            $initialModalCount + 1,
            (int)$modalState['totalModalCount'],
            json_encode($modalState, JSON_THROW_ON_ERROR),
        );
    }
}
