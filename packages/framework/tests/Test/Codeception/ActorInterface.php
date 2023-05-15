<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Codeception;

use Closure;
use Codeception\TestInterface;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

interface ActorInterface
{
    public function acceptPopup(): void;

    /**
     * @param string $databaseKey
     */
    public function amConnectedToDatabase(string $databaseKey): void;

    /**
     * @param string $routeName
     * @param array $parameters
     */
    public function amOnLocalizedRoute(string $routeName, array $parameters = []): void;

    /**
     * @param mixed $page
     */
    public function amOnPage($page): void;

    /**
     * @param string $subdomain
     */
    public function amOnSubdomain(string $subdomain): void;

    /**
     * @param mixed $url
     */
    public function amOnUrl($url): void;

    /**
     * @param mixed $field
     * @param string $value
     */
    public function appendField($field, string $value): void;

    /**
     * @param mixed $field
     * @param string $filename
     */
    public function attachFile($field, string $filename): void;

    /**
     * @param mixed $text
     * @param mixed|null $selector
     */
    public function canSee($text, $selector = null): void;

    /**
     * @param mixed $checkbox
     */
    public function canSeeCheckboxIsChecked($checkbox): void;

    /**
     * @param string $checkboxId
     */
    public function canSeeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function canSeeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param mixed $cookie
     * @param array $params
     * @param bool $showDebug
     */
    public function canSeeCookie($cookie, array $params = [], bool $showDebug = true): void;

    /**
     * @param mixed $page
     */
    public function canSeeCurrentPageEquals($page);

    /**
     * @param string $uri
     */
    public function canSeeCurrentUrlEquals(string $uri): void;

    /**
     * @param string $uri
     */
    public function canSeeCurrentUrlMatches(string $uri): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function canSeeElement($selector, array $attributes = []): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function canSeeElementInDOM($selector, array $attributes = []): void;

    /**
     * @param string $text
     * @param string $css
     */
    public function canSeeInCss(string $text, string $css): void;

    /**
     * @param string $uri
     */
    public function canSeeInCurrentUrl(string $uri): void;

    /**
     * @param string $table
     * @param array $criteria
     */
    public function canSeeInDatabase(string $table, array $criteria = []): void;

    /**
     * @param string $text
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function canSeeInElement(string $text, WebDriverElement $element): void;

    /**
     * @param mixed $field
     * @param mixed $value
     */
    public function canSeeInField($field, $value): void;

    /**
     * @param string $value
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function canSeeInFieldByElement(string $value, WebDriverElement $element): void;

    /**
     * @param string $value
     * @param string $fieldName
     */
    public function canSeeInFieldByName(string $value, string $fieldName): void;

    /**
     * @param mixed $formSelector
     * @param array $params
     */
    public function canSeeInFormFields($formSelector, array $params): void;

    /**
     * @param string $text
     */
    public function canSeeInPageSource(string $text): void;

    /**
     * @param string $text
     */
    public function canSeeInPopup(string $text): void;

    /**
     * @param mixed $raw
     */
    public function canSeeInSource($raw): void;

    /**
     * @param mixed $title
     */
    public function canSeeInTitle($title);

    /**
     * @param string $text
     * @param string|null $url
     */
    public function canSeeLink(string $text, ?string $url = null): void;

    /**
     * @param int $expectedNumber
     * @param string $table
     * @param array $criteria
     */
    public function canSeeNumRecords(int $expectedNumber, string $table, array $criteria = []): void;

    /**
     * @param mixed $selector
     * @param mixed $expected
     */
    public function canSeeNumberOfElements($selector, $expected): void;

    /**
     * @param mixed $selector
     * @param mixed $expected
     */
    public function canSeeNumberOfElementsInDOM($selector, $expected);

    /**
     * @param int $number
     */
    public function canSeeNumberOfTabs(int $number): void;

    /**
     * @param mixed $selector
     * @param mixed $optionText
     */
    public function canSeeOptionIsSelected($selector, $optionText): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function canSeeTranslationAdmin(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param string $id
     * @param string $css
     * @param string $translationDomain
     * @param array $parameters
     */
    public function canSeeTranslationAdminInCss(string $id, string $css, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function canSeeTranslationFrontend(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    public function cancelPopup(): void;

    /**
     * @param mixed $text
     * @param mixed|null $selector
     */
    public function cantSee($text, $selector = null): void;

    /**
     * @param mixed $checkbox
     */
    public function cantSeeCheckboxIsChecked($checkbox): void;

    /**
     * @param string $checkboxId
     */
    public function cantSeeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function cantSeeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param mixed $cookie
     * @param array $params
     * @param bool $showDebug
     */
    public function cantSeeCookie($cookie, array $params = [], bool $showDebug = true): void;

    /**
     * @param string $uri
     */
    public function cantSeeCurrentUrlEquals(string $uri): void;

    /**
     * @param string $uri
     */
    public function cantSeeCurrentUrlMatches(string $uri): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function cantSeeElement($selector, array $attributes = []): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function cantSeeElementInDOM($selector, array $attributes = []): void;

    /**
     * @param string $uri
     */
    public function cantSeeInCurrentUrl(string $uri): void;

    /**
     * @param string $table
     * @param array $criteria
     */
    public function cantSeeInDatabase(string $table, array $criteria = []): void;

    /**
     * @param mixed $field
     * @param mixed $value
     */
    public function cantSeeInField($field, $value): void;

    /**
     * @param mixed $formSelector
     * @param array $params
     */
    public function cantSeeInFormFields($formSelector, array $params): void;

    /**
     * @param string $text
     */
    public function cantSeeInPageSource(string $text): void;

    /**
     * @param string $text
     */
    public function cantSeeInPopup(string $text): void;

    /**
     * @param mixed $raw
     */
    public function cantSeeInSource($raw): void;

    /**
     * @param mixed $title
     */
    public function cantSeeInTitle($title);

    /**
     * @param string $text
     * @param string $url
     */
    public function cantSeeLink(string $text, string $url = ''): void;

    /**
     * @param mixed $selector
     * @param mixed $optionText
     */
    public function cantSeeOptionIsSelected($selector, $optionText): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function cantSeeTranslationFrontend(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function checkElement(WebDriverElement $element): void;

    /**
     * @param mixed $option
     */
    public function checkOption($option): void;

    /**
     * @param string $optionId
     */
    public function checkOptionById(string $optionId): void;

    /**
     * @param string $label
     */
    public function checkOptionByLabel(string $label): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function checkOptionByLabelTranslationFrontend(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    public function cleanup();

    /**
     * @param mixed $field
     */
    public function clearField($field): void;

    /**
     * @param mixed $link
     * @param mixed|null $context
     */
    public function click($link, $context = null): void;

    /**
     * @param string $css
     * @param mixed|null $contextSelector
     */
    public function clickByCss(string $css, $contextSelector = null): void;

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function clickByElement(WebDriverElement $element): WebDriverElement;

    /**
     * @param string $name
     * @param mixed|null $contextSelector
     */
    public function clickByName(string $name, $contextSelector = null): void;

    /**
     * @param string $text
     * @param mixed|null $contextSelector
     */
    public function clickByText(string $text, $contextSelector = null): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null|null $contextSelector
     */
    public function clickByTranslationAdmin(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = [], WebDriverBy|WebDriverElement|null $contextSelector = null): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null|null $contextSelector
     */
    public function clickByTranslationFrontend(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = [], WebDriverBy|WebDriverElement|null $contextSelector = null);

    /**
     * @param mixed|null $cssOrXPath
     * @param int|null $offsetX
     * @param int|null $offsetY
     */
    public function clickWithLeftButton($cssOrXPath = null, ?int $offsetX = null, ?int $offsetY = null): void;

    /**
     * @param mixed|null $cssOrXPath
     * @param int|null $offsetX
     * @param int|null $offsetY
     */
    public function clickWithRightButton($cssOrXPath = null, ?int $offsetX = null, ?int $offsetY = null): void;

    public function closeTab(): void;

    /**
     * @param string $css
     * @return int
     */
    public function countVisibleByCss(string $css): int;

    /**
     * @param \Codeception\TestInterface|null $test
     */
    public function debugWebDriverLogs(?TestInterface $test = null): void;

    /**
     * @param mixed $name
     */
    public function deleteSessionSnapshot($name);

    /**
     * @param mixed $text
     * @param mixed|null $selector
     */
    public function dontSee($text, $selector = null): void;

    /**
     * @param mixed $checkbox
     */
    public function dontSeeCheckboxIsChecked($checkbox): void;

    /**
     * @param string $checkboxId
     */
    public function dontSeeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function dontSeeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param mixed $cookie
     * @param array $params
     * @param bool $showDebug
     */
    public function dontSeeCookie($cookie, array $params = [], bool $showDebug = true): void;

    /**
     * @param string $uri
     */
    public function dontSeeCurrentUrlEquals(string $uri): void;

    /**
     * @param string $uri
     */
    public function dontSeeCurrentUrlMatches(string $uri): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function dontSeeElement($selector, array $attributes = []): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function dontSeeElementInDOM($selector, array $attributes = []): void;

    /**
     * @param string $uri
     */
    public function dontSeeInCurrentUrl(string $uri): void;

    /**
     * @param string $table
     * @param array $criteria
     */
    public function dontSeeInDatabase(string $table, array $criteria = []): void;

    /**
     * @param mixed $field
     * @param mixed $value
     */
    public function dontSeeInField($field, $value): void;

    /**
     * @param mixed $formSelector
     * @param array $params
     */
    public function dontSeeInFormFields($formSelector, array $params): void;

    /**
     * @param string $text
     */
    public function dontSeeInPageSource(string $text): void;

    /**
     * @param string $text
     */
    public function dontSeeInPopup(string $text): void;

    /**
     * @param mixed $raw
     */
    public function dontSeeInSource($raw): void;

    /**
     * @param mixed $title
     */
    public function dontSeeInTitle($title);

    /**
     * @param string $text
     * @param string $url
     */
    public function dontSeeLink(string $text, string $url = ''): void;

    /**
     * @param mixed $selector
     * @param mixed $optionText
     */
    public function dontSeeOptionIsSelected($selector, $optionText): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function dontSeeTranslationFrontend(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param mixed $cssOrXPath
     */
    public function doubleClick($cssOrXPath): void;

    /**
     * @param mixed $source
     * @param mixed $target
     */
    public function dragAndDrop($source, $target): void;

    /**
     * @param string $script
     * @param array $arguments
     */
    public function executeAsyncJS(string $script, array $arguments = []);

    /**
     * @param \Closure $function
     */
    public function executeInSelenium(Closure $function);

    /**
     * @param string $script
     * @param array $arguments
     */
    public function executeJS(string $script, array $arguments = []);

    /**
     * @param mixed $field
     * @param mixed $value
     */
    public function fillField($field, $value): void;

    /**
     * @param string $css
     * @param string $value
     */
    public function fillFieldByCss(string $css, string $value): void;

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param string $value
     */
    public function fillFieldByElement(WebDriverElement $element, string $value): void;

    /**
     * @param string $fieldName
     * @param string $value
     */
    public function fillFieldByName(string $fieldName, string $value): void;

    /**
     * @param string $css
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function findElementByCss(string $css): WebDriverElement;

    /**
     * @return string
     */
    public function getAdminLocale(): string;

    /**
     * @return string
     */
    public function getDefaultUnitName(): string;

    /**
     * @param string $number
     * @return string
     */
    public function getFormattedPercentAdmin(string $number): string;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceRoundedByCurrencyOnFrontend(Money $price): string;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money $price): string;

    /**
     * @return string
     */
    public function getFrontendLocale(): string;

    /**
     * @param string $number
     * @param string $locale
     * @return string
     */
    public function getNumberFromLocalizedFormat(string $number, string $locale): string;

    /**
     * @param string $price
     * @return string
     */
    public function getPriceWithVatConvertedToDomainDefaultCurrency(string $price): string;

    /**
     * @param mixed $cssOrXpath
     * @param mixed $attribute
     * @return string|null
     */
    public function grabAttributeFrom($cssOrXpath, $attribute): ?string;

    /**
     * @param string $table
     * @param string $column
     * @param array $criteria
     * @return array
     */
    public function grabColumnFromDatabase(string $table, string $column, array $criteria = []): array;

    /**
     * @param mixed $cookie
     * @param array $params
     */
    public function grabCookie($cookie, array $params = []): mixed;

    /**
     * @param string $table
     * @param array $criteria
     * @return array
     */
    public function grabEntriesFromDatabase(string $table, array $criteria = []): array;

    /**
     * @param string $table
     * @param array $criteria
     * @return array
     */
    public function grabEntryFromDatabase(string $table, array $criteria = []): array;

    /**
     * @param mixed|null $uri
     */
    public function grabFromCurrentUrl($uri = null): mixed;

    /**
     * @param string $table
     * @param string $column
     * @param array $criteria
     */
    public function grabFromDatabase(string $table, string $column, array $criteria = []);

    /**
     * @param mixed $cssOrXpath
     * @param mixed|null $attribute
     * @return array
     */
    public function grabMultiple($cssOrXpath, $attribute = null): array;

    /**
     * @param string $table
     * @param array $criteria
     * @return int
     */
    public function grabNumRecords(string $table, array $criteria = []): int;

    /**
     * @return string
     */
    public function grabPageSource(): string;

    /**
     * @param mixed $serviceId
     */
    public function grabServiceFromContainer($serviceId);

    /**
     * @param mixed $cssOrXPathOrRegex
     */
    public function grabTextFrom($cssOrXPathOrRegex): mixed;

    /**
     * @param mixed $field
     * @return string|null
     */
    public function grabValueFrom($field): ?string;

    /**
     * @param string $table
     * @param array $data
     * @return int
     */
    public function haveInDatabase(string $table, array $data): int;

    /**
     * @param mixed $name
     * @param bool $showDebug
     * @return bool
     */
    public function loadSessionSnapshot($name, bool $showDebug = true): bool;

    /**
     * @param mixed $selector
     * @param string|null $name
     */
    public function makeElementScreenshot($selector, ?string $name = null): void;

    /**
     * @param string|null $name
     */
    public function makeHtmlSnapshot(?string $name = null): void;

    /**
     * @param string|null $name
     */
    public function makeScreenshot(?string $name = null): void;

    public function maximizeWindow(): void;

    public function moveBack(): void;

    public function moveForward(): void;

    /**
     * @param mixed|null $cssOrXPath
     * @param int|null $offsetX
     * @param int|null $offsetY
     */
    public function moveMouseOver($cssOrXPath = null, ?int $offsetX = null, ?int $offsetY = null): void;

    public function openNewTab(): void;

    /**
     * @param mixed $databaseKey
     * @param mixed $actions
     */
    public function performInDatabase($databaseKey, $actions): void;

    /**
     * @param mixed $element
     * @param mixed $actions
     * @param int $timeout
     */
    public function performOn($element, $actions, int $timeout = 10): void;

    /**
     * @param mixed $element
     * @param mixed|null $chars
     */
    public function pressKey($element, $chars = null): void;

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param mixed $keys
     */
    public function pressKeysByElement(WebDriverElement $element, $keys): void;

    public function reloadPage(): void;

    /**
     * @param mixed $cookie
     * @param array $params
     * @param bool $showDebug
     */
    public function resetCookie($cookie, array $params = [], bool $showDebug = true): void;

    /**
     * @param int $width
     * @param int $height
     */
    public function resizeWindow(int $width, int $height): void;

    /**
     * @param mixed $name
     */
    public function saveSessionSnapshot($name);

    /**
     * @param mixed $selector
     * @param int|null $offsetX
     * @param int|null $offsetY
     */
    public function scrollTo($selector, ?int $offsetX = null, ?int $offsetY = null): void;

    /**
     * @param \Facebook\WebDriver\WebDriverElement $webDriverElement
     */
    public function scrollToElement(WebDriverElement $webDriverElement): void;

    /**
     * @param mixed $text
     * @param mixed|null $selector
     */
    public function see($text, $selector = null): void;

    /**
     * @param mixed $checkbox
     */
    public function seeCheckboxIsChecked($checkbox): void;

    /**
     * @param string $checkboxId
     */
    public function seeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function seeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param mixed $cookie
     * @param array $params
     * @param bool $showDebug
     */
    public function seeCookie($cookie, array $params = [], bool $showDebug = true): void;

    /**
     * @param mixed $page
     */
    public function seeCurrentPageEquals($page);

    /**
     * @param string $uri
     */
    public function seeCurrentUrlEquals(string $uri): void;

    /**
     * @param string $uri
     */
    public function seeCurrentUrlMatches(string $uri): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function seeElement($selector, array $attributes = []): void;

    /**
     * @param mixed $selector
     * @param array $attributes
     */
    public function seeElementInDOM($selector, array $attributes = []): void;

    /**
     * @param string $text
     * @param string $css
     */
    public function seeInCss(string $text, string $css): void;

    /**
     * @param string $uri
     */
    public function seeInCurrentUrl(string $uri): void;

    /**
     * @param string $table
     * @param array $criteria
     */
    public function seeInDatabase(string $table, array $criteria = []): void;

    /**
     * @param string $text
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function seeInElement(string $text, WebDriverElement $element): void;

    /**
     * @param mixed $field
     * @param mixed $value
     */
    public function seeInField($field, $value): void;

    /**
     * @param string $value
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function seeInFieldByElement(string $value, WebDriverElement $element): void;

    /**
     * @param string $value
     * @param string $fieldName
     */
    public function seeInFieldByName(string $value, string $fieldName): void;

    /**
     * @param mixed $formSelector
     * @param array $params
     */
    public function seeInFormFields($formSelector, array $params): void;

    /**
     * @param string $text
     */
    public function seeInPageSource(string $text): void;

    /**
     * @param string $text
     */
    public function seeInPopup(string $text): void;

    /**
     * @param mixed $raw
     */
    public function seeInSource($raw): void;

    /**
     * @param mixed $title
     */
    public function seeInTitle($title);

    /**
     * @param string $text
     * @param string|null $url
     */
    public function seeLink(string $text, ?string $url = null): void;

    /**
     * @param int $expectedNumber
     * @param string $table
     * @param array $criteria
     */
    public function seeNumRecords(int $expectedNumber, string $table, array $criteria = []): void;

    /**
     * @param mixed $selector
     * @param mixed $expected
     */
    public function seeNumberOfElements($selector, $expected): void;

    /**
     * @param mixed $selector
     * @param mixed $expected
     */
    public function seeNumberOfElementsInDOM($selector, $expected);

    /**
     * @param int $number
     */
    public function seeNumberOfTabs(int $number): void;

    /**
     * @param mixed $selector
     * @param mixed $optionText
     */
    public function seeOptionIsSelected($selector, $optionText): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function seeTranslationAdmin(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param string $id
     * @param string $css
     * @param string $translationDomain
     * @param array $parameters
     */
    public function seeTranslationAdminInCss(string $id, string $css, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param string $id
     * @param string $translationDomain
     * @param array $parameters
     */
    public function seeTranslationFrontend(string $id, string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN, array $parameters = []): void;

    /**
     * @param mixed $select
     * @param mixed $option
     */
    public function selectOption($select, $option): void;

    /**
     * @param string $selectCss
     * @param string $optionValue
     */
    public function selectOptionByCssAndValue(string $selectCss, string $optionValue): void;

    /**
     * @param mixed $name
     * @param mixed $value
     * @param array $params
     * @param mixed $showDebug
     */
    public function setCookie($name, $value, array $params = [], $showDebug = true): void;

    /**
     * @param mixed $selector
     * @param array $params
     * @param mixed|null $button
     */
    public function submitForm($selector, array $params, $button = null): void;

    /**
     * @param string|null $locator
     */
    public function switchToFrame(?string $locator = null): void;

    /**
     * @param string|null $locator
     */
    public function switchToIFrame(?string $locator = null): void;

    /**
     * @param int $offset
     */
    public function switchToNextTab(int $offset = 1): void;

    /**
     * @param int $offset
     */
    public function switchToPreviousTab(int $offset = 1): void;

    /**
     * @param string|null $name
     */
    public function switchToWindow(?string $name = null): void;

    /**
     * @param string $text
     * @param int $delay
     */
    public function type(string $text, int $delay = 0): void;

    /**
     * @param string $keys
     */
    public function typeInPopup(string $keys): void;

    /**
     * @param mixed $option
     */
    public function uncheckOption($option): void;

    /**
     * @param mixed $select
     * @param mixed $option
     */
    public function unselectOption($select, $option): void;

    /**
     * @param string $table
     * @param array $data
     * @param array $criteria
     */
    public function updateInDatabase(string $table, array $data, array $criteria = []): void;

    /**
     * @param mixed $timeout
     */
    public function wait($timeout): void;

    /**
     * @param mixed $element
     * @param int $timeout
     */
    public function waitForElement($element, int $timeout = 10): void;

    /**
     * @param mixed $element
     * @param \Closure $callback
     * @param int $timeout
     */
    public function waitForElementChange($element, Closure $callback, int $timeout = 30): void;

    /**
     * @param mixed $element
     * @param int $timeout
     */
    public function waitForElementClickable($element, int $timeout = 10): void;

    /**
     * @param mixed $element
     * @param int $timeout
     */
    public function waitForElementNotVisible($element, int $timeout = 10): void;

    /**
     * @param mixed $element
     * @param int $timeout
     */
    public function waitForElementVisible($element, int $timeout = 10): void;

    /**
     * @param string $script
     * @param int $timeout
     */
    public function waitForJS(string $script, int $timeout = 5): void;

    /**
     * @param string $text
     * @param int $timeout
     * @param mixed|null $selector
     */
    public function waitForText(string $text, int $timeout = 10, $selector = null): void;
}
