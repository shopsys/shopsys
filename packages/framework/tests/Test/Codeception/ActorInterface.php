<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Codeception;

use Closure;
use Codeception\TestInterface;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

interface ActorInterface
{
    public function acceptPopup(): void;

    public function amConnectedToDatabase(string $databaseKey): void;

    public function amOnLocalizedRoute(string $routeName, array $parameters = []): void;

    public function amOnPage(mixed $page): void;

    public function amOnSubdomain(string $subdomain): void;

    public function amOnUrl(mixed $url): void;

    public function appendField(mixed $field, string $value): void;

    public function attachFile(mixed $field, string $filename): void;

    public function canSee(mixed $text, mixed $selector = null): void;

    public function canSeeCheckboxIsChecked(mixed $checkbox): void;

    public function canSeeCheckboxIsCheckedById(string $checkboxId): void;

    public function canSeeCheckboxIsCheckedByLabel(string $label): void;

    public function canSeeCookie(mixed $cookie, array $params = [], bool $showDebug = true): void;

    public function canSeeCurrentPageEquals(string $page): void;

    public function canSeeCurrentUrlEquals(string $uri): void;

    public function canSeeCurrentUrlMatches(string $uri): void;

    public function canSeeElement(mixed $selector, array $attributes = []): void;

    public function canSeeElementInDOM(mixed $selector, array $attributes = []): void;

    public function canSeeInCss(string $text, string $css): void;

    public function canSeeInCurrentUrl(string $uri): void;

    public function canSeeInDatabase(string $table, array $criteria = []): void;

    public function canSeeInElement(string $text, WebDriverElement $element): void;

    public function canSeeInField(mixed $field, mixed $value): void;

    public function canSeeInFieldByElement(string $value, WebDriverElement $element): void;

    public function canSeeInFieldByName(string $value, string $fieldName): void;

    public function canSeeInFormFields(mixed $formSelector, array $params): void;

    public function canSeeInPageSource(string $text): void;

    public function canSeeInPopup(string $text): void;

    public function canSeeInSource(mixed $raw): void;

    public function canSeeInTitle(mixed $title);

    public function canSeeLink(string $text, ?string $url = null): void;

    public function canSeeNumRecords(int $expectedNumber, string $table, array $criteria = []): void;

    public function canSeeNumberOfElements(mixed $selector, mixed $expected): void;

    public function canSeeNumberOfElementsInDOM(mixed $selector, mixed $expected);

    public function canSeeNumberOfTabs(int $number): void;

    public function canSeeOptionIsSelected(mixed $selector, mixed $optionText): void;

    public function canSeeTranslationAdmin(
        string $id,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
    ): void;

    public function canSeeTranslationAdminInCss(
        string $id,
        string $css,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
    ): void;

    public function cancelPopup(): void;

    public function cantSee(mixed $text, mixed $selector = null): void;

    public function cantSeeCheckboxIsChecked(mixed $checkbox): void;

    public function cantSeeCheckboxIsCheckedById(string $checkboxId): void;

    public function cantSeeCheckboxIsCheckedByLabel(string $label): void;

    public function cantSeeCookie(mixed $cookie, array $params = [], bool $showDebug = true): void;

    public function cantSeeCurrentUrlEquals(string $uri): void;

    public function cantSeeCurrentUrlMatches(string $uri): void;

    public function cantSeeElement(mixed $selector, array $attributes = []): void;

    public function cantSeeElementInDOM(mixed $selector, array $attributes = []): void;

    public function cantSeeInCurrentUrl(string $uri): void;

    public function cantSeeInDatabase(string $table, array $criteria = []): void;

    public function cantSeeInField(mixed $field, mixed $value): void;

    public function cantSeeInFormFields(mixed $formSelector, array $params): void;

    public function cantSeeInPageSource(string $text): void;

    public function cantSeeInPopup(string $text): void;

    public function cantSeeInSource(mixed $raw): void;

    public function cantSeeInTitle(mixed $title);

    public function cantSeeLink(string $text, string $url = ''): void;

    public function cantSeeOptionIsSelected(mixed $selector, mixed $optionText): void;

    public function checkElement(WebDriverElement $element): void;

    public function checkOption(mixed $option): void;

    public function checkOptionById(string $optionId): void;

    public function checkOptionByLabel(string $label): void;

    public function cleanup();

    public function clearField(mixed $field): void;

    public function click(mixed $link, mixed $context = null): void;

    public function clickByCss(string $css, WebDriverBy|WebDriverElement|null $contextSelector = null): void;

    public function clickByElement(WebDriverElement $element): WebDriverElement;

    public function clickByName(string $name, WebDriverBy|WebDriverElement|null $contextSelector = null): void;

    public function clickByText(string $text, WebDriverBy|WebDriverElement|null $contextSelector = null): void;

    public function clickByTranslationAdmin(
        string $id,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
        WebDriverBy|WebDriverElement|null $contextSelector = null,
    ): void;

    public function clickWithLeftButton(mixed $cssOrXPath = null, ?int $offsetX = null, ?int $offsetY = null): void;

    public function clickWithRightButton(mixed $cssOrXPath = null, ?int $offsetX = null, ?int $offsetY = null): void;

    public function closeTab(): void;

    public function countVisibleByCss(string $css): int;

    public function debugWebDriverLogs(?TestInterface $test = null): void;

    public function deleteSessionSnapshot(mixed $name);

    public function dontSee(mixed $text, mixed $selector = null): void;

    public function dontSeeCheckboxIsChecked(mixed $checkbox): void;

    public function dontSeeCheckboxIsCheckedById(string $checkboxId): void;

    public function dontSeeCheckboxIsCheckedByLabel(string $label): void;

    public function dontSeeCookie(mixed $cookie, array $params = [], bool $showDebug = true): void;

    public function dontSeeCurrentUrlEquals(string $uri): void;

    public function dontSeeCurrentUrlMatches(string $uri): void;

    public function dontSeeElement(mixed $selector, array $attributes = []): void;

    public function dontSeeElementInDOM(mixed $selector, array $attributes = []): void;

    public function dontSeeInCurrentUrl(string $uri): void;

    public function dontSeeInDatabase(string $table, array $criteria = []): void;

    public function dontSeeInField(mixed $field, mixed $value): void;

    public function dontSeeInFormFields(mixed $formSelector, array $params): void;

    public function dontSeeInPageSource(string $text): void;

    public function dontSeeInPopup(string $text): void;

    public function dontSeeInSource(mixed $raw): void;

    public function dontSeeInTitle(mixed $title);

    public function dontSeeLink(string $text, string $url = ''): void;

    public function dontSeeOptionIsSelected(mixed $selector, mixed $optionText): void;

    public function doubleClick(mixed $cssOrXPath): void;

    public function dragAndDrop(mixed $source, mixed $target): void;

    public function executeAsyncJS(string $script, array $arguments = []);

    public function executeInSelenium(Closure $function);

    public function executeJS(string $script, array $arguments = []);

    public function fillField(mixed $field, mixed $value): void;

    public function fillFieldByCss(string $css, string $value): void;

    public function fillFieldByElement(WebDriverElement $element, string $value): void;

    public function fillFieldByName(string $fieldName, string $value): void;

    public function findElementByCss(string $css): WebDriverElement;

    public function getAdminLocale(): string;

    public function getDefaultUnitName(): string;

    public function getFormattedPercentAdmin(string $number): string;

    public function getNumberFromLocalizedFormat(string $number, string $locale): string;

    public function getPriceWithVatConvertedToDomainDefaultCurrency(string $price): string;

    public function grabAttributeFrom(mixed $cssOrXpath, mixed $attribute): ?string;

    public function grabColumnFromDatabase(string $table, string $column, array $criteria = []): array;

    public function grabCookie(mixed $cookie, array $params = []): mixed;

    public function grabEntriesFromDatabase(string $table, array $criteria = []): array;

    public function grabEntryFromDatabase(string $table, array $criteria = []): array;

    public function grabFromCurrentUrl(mixed $uri = null): mixed;

    public function grabFromDatabase(string $table, string $column, array $criteria = []);

    public function grabMultiple(mixed $cssOrXpath, mixed $attribute = null): array;

    public function grabNumRecords(string $table, array $criteria = []): int;

    public function grabPageSource(): string;

    public function grabServiceFromContainer(string $serviceId): object;

    public function grabTextFrom(mixed $cssOrXPathOrRegex): mixed;

    public function grabValueFrom(mixed $field): ?string;

    public function haveInDatabase(string $table, array $data): int;

    public function loadSessionSnapshot(mixed $name, bool $showDebug = true): bool;

    public function makeElementScreenshot(mixed $selector, ?string $name = null): void;

    public function makeHtmlSnapshot(?string $name = null): void;

    public function makeScreenshot(?string $name = null): void;

    public function maximizeWindow(): void;

    public function moveBack(): void;

    public function moveForward(): void;

    public function moveMouseOver(mixed $cssOrXPath = null, ?int $offsetX = null, ?int $offsetY = null): void;

    public function openNewTab(): void;

    public function performInDatabase(mixed $databaseKey, mixed $actions): void;

    public function performOn(mixed $element, mixed $actions, int $timeout = 10): void;

    public function pressKey(mixed $element, mixed $chars = null): void;

    public function pressKeysByElement(WebDriverElement $element, string|array $keys): void;

    public function reloadPage(): void;

    public function resetCookie(mixed $cookie, array $params = [], bool $showDebug = true): void;

    public function resizeWindow(int $width, int $height): void;

    public function saveSessionSnapshot(mixed $name);

    public function scrollTo(mixed $selector, ?int $offsetX = null, ?int $offsetY = null): void;

    public function scrollToElement(WebDriverElement $webDriverElement): void;

    public function see(mixed $text, mixed $selector = null): void;

    public function seeCheckboxIsChecked(mixed $checkbox): void;

    public function seeCheckboxIsCheckedById(string $checkboxId): void;

    public function seeCheckboxIsCheckedByLabel(string $label): void;

    public function seeCookie(mixed $cookie, array $params = [], bool $showDebug = true): void;

    public function seeCurrentPageEquals(string $page): void;

    public function seeCurrentUrlEquals(string $uri): void;

    public function seeCurrentUrlMatches(string $uri): void;

    public function seeElement(mixed $selector, array $attributes = []): void;

    public function seeElementInDOM(mixed $selector, array $attributes = []): void;

    public function seeInCss(string $text, string $css): void;

    public function seeInCurrentUrl(string $uri): void;

    public function seeInDatabase(string $table, array $criteria = []): void;

    public function seeInElement(string $text, WebDriverElement $element): void;

    public function seeInField(mixed $field, mixed $value): void;

    public function seeInFieldByElement(string $value, WebDriverElement $element): void;

    public function seeInFieldByName(string $value, string $fieldName): void;

    public function seeInFormFields(mixed $formSelector, array $params): void;

    public function seeInPageSource(string $text): void;

    public function seeInPopup(string $text): void;

    public function seeInSource(mixed $raw): void;

    public function seeInTitle(mixed $title);

    public function seeLink(string $text, ?string $url = null): void;

    public function seeNumRecords(int $expectedNumber, string $table, array $criteria = []): void;

    public function seeNumberOfElements(mixed $selector, mixed $expected): void;

    public function seeNumberOfElementsInDOM(mixed $selector, mixed $expected);

    public function seeNumberOfTabs(int $number): void;

    public function seeOptionIsSelected(mixed $selector, mixed $optionText): void;

    public function seeTranslationAdmin(
        string $id,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
    ): void;

    public function seeTranslationAdminInCss(
        string $id,
        string $css,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
    ): void;

    public function selectOption(mixed $select, mixed $option): void;

    public function selectOptionByCssAndValue(string $selectCss, string $optionValue): void;

    public function setCookie(mixed $name, mixed $value, array $params = [], mixed $showDebug = true): void;

    public function submitForm(mixed $selector, array $params, mixed $button = null): void;

    public function switchToFrame(?string $locator = null): void;

    public function switchToIFrame(?string $locator = null): void;

    public function switchToNextTab(int $offset = 1): void;

    public function switchToPreviousTab(int $offset = 1): void;

    public function switchToWindow(?string $name = null): void;

    public function type(string $text, int $delay = 0): void;

    public function typeInPopup(string $keys): void;

    public function uncheckOption(mixed $option): void;

    public function unselectOption(mixed $select, mixed $option): void;

    public function updateInDatabase(string $table, array $data, array $criteria = []): void;

    public function wait(mixed $timeout): void;

    public function waitForElement(mixed $element, int $timeout = 10): void;

    public function waitForElementChange(mixed $element, Closure $callback, int $timeout = 30): void;

    public function waitForElementClickable(mixed $element, int $timeout = 10): void;

    public function waitForElementNotVisible(mixed $element, int $timeout = 10): void;

    public function waitForElementVisible(mixed $element, int $timeout = 10): void;

    public function waitForJS(string $script, int $timeout = 5): void;

    public function waitForText(string $text, int $timeout = 10, mixed $selector = null): void;
}
