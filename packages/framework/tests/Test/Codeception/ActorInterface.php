<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Codeception;

use Closure;
use Codeception\TestInterface;
use Facebook\WebDriver\WebDriverElement;

interface ActorInterface
{
    public function acceptPopup();

    /**
     * @param string $role
     */
    public function am(string $role);

    /**
     * @param string $databaseKey
     */
    public function amConnectedToDatabase(string $databaseKey);

    /**
     * @param string $page
     */
    public function amOnPage(string $page);

    /**
     * @param string $subdomain
     * @return mixed
     */
    public function amOnSubdomain(string $subdomain);

    /**
     * @param string $url
     */
    public function amOnUrl(string $url);

    /**
     * @param string $field
     * @param string $value
     */
    public function appendField(string $field, string $value);

    /**
     * @param string $field
     * @param string $filename
     */
    public function attachFile(string $field, string $filename);

    /**
     * @param string $text
     * @param array|string $selector optional
     */
    public function canSee(string $text, array|string $selector = null);

    /**
     * @param string $checkboxId
     */
    public function canSeeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function canSeeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param string $cookie
     * @param array $params
     */
    public function canSeeCookie(string $cookie, array $params = []);

    /**
     * @param string $page
     */
    public function canSeeCurrentPageEquals(string $page);

    /**
     * @param string $uri
     */
    public function canSeeCurrentUrlEquals(string $uri);

    /**
     * @param string $uri
     */
    public function canSeeCurrentUrlMatches(string $uri);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function canSeeElement(array|string $selector, array $attributes = []);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function canSeeElementInDOM(array|string $selector, array $attributes = []);

    /**
     * @param string $text
     * @param string $css
     */
    public function canSeeInCss(string $text, string $css): void;

    /**
     * @param string $uri
     */
    public function canSeeInCurrentUrl(string $uri);

    /**
     * @param string $table
     * @param array $criteria
     */
    public function canSeeInDatabase(string $table, array $criteria = null);

    /**
     * @param string $text
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function canSeeInElement(string $text, WebDriverElement $element);

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
     * @param array|string $formSelector
     * @param array $params
     */
    public function canSeeInFormFields(array|string $formSelector, array $params);

    /**
     * @param string $text
     */
    public function canSeeInPageSource(string $text);

    /**
     * @param string $text
     */
    public function canSeeInPopup(string $text);

    /**
     * @param string $raw
     */
    public function canSeeInSource(string $raw);

    /**
     * @param string $title
     */
    public function canSeeInTitle(string $title);

    /**
     * @param string $text
     * @param string $url optional
     */
    public function canSeeLink(string $text, string $url = null);

    /**
     * @param int $expectedNumber Expected number
     * @param string $table Table name
     * @param array $criteria Search criteria [Optional]
     */
    public function canSeeNumRecords(int $expectedNumber, string $table, array $criteria = []);

    /**
     * @param array|string $selector
     * @param mixed $expected int or int[]
     */
    public function canSeeNumberOfElements(array|string $selector, mixed $expected);

    /**
     * @param array|string $selector
     * @param mixed $expected int or int[]
     */
    public function canSeeNumberOfElementsInDOM(array|string $selector, mixed $expected);

    /**
     * @param array|string $selector
     * @param string $optionText
     */
    public function canSeeOptionIsSelected(array|string $selector, string $optionText);

    public function cancelPopup();

    /**
     * @param string $text
     * @param array|string $selector optional
     */
    public function cantSee(string $text, array|string $selector = null);

    /**
     * @param string $checkboxId
     */
    public function cantSeeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function cantSeeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param string $cookie
     * @param array $params
     */
    public function cantSeeCookie(string $cookie, array $params = []);

    /**
     * @param string $uri
     */
    public function cantSeeCurrentUrlEquals(string $uri);

    /**
     * @param string $uri
     */
    public function cantSeeCurrentUrlMatches(string $uri);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function cantSeeElement(array|string $selector, array $attributes = []);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function cantSeeElementInDOM(array|string $selector, array $attributes = []);

    /**
     * @param string $uri
     */
    public function cantSeeInCurrentUrl(string $uri);

    /**
     * @param string $table
     * @param array $criteria
     */
    public function cantSeeInDatabase(string $table, array $criteria = []);

    /**
     * @param string $field
     * @param string $value
     */
    public function cantSeeInField(string $field, string $value);

    /**
     * @param array|string $formSelector
     * @param array $params
     */
    public function cantSeeInFormFields(array|string $formSelector, array $params);

    /**
     * @param string $text
     */
    public function cantSeeInPageSource(string $text);

    /**
     * @param string $text
     */
    public function cantSeeInPopup(string $text);

    /**
     * @param string $raw
     */
    public function cantSeeInSource(string $raw);

    /**
     * @param string $title
     */
    public function cantSeeInTitle(string $title);

    /**
     * @param string $text
     * @param string $url optional
     */
    public function cantSeeLink(string $text, string $url = null);

    /**
     * @param array|string $selector
     * @param string $optionText
     */
    public function cantSeeOptionIsSelected(array|string $selector, string $optionText);

    /**
     * @param string $optionId
     */
    public function checkOptionById(string $optionId): void;

    /**
     * @param string $label
     */
    public function checkOptionByLabel(string $label): void;

    public function cleanup();

    /**
     * @param string $field
     */
    public function clearField(string $field);

    /**
     * @param string $css
     * @param mixed|null $contextSelector
     */
    public function clickByCss(string $css, ?mixed $contextSelector = null): void;

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @return \Facebook\WebDriver\WebDriverElement
     */
    public function clickByElement(WebDriverElement $element): WebDriverElement;

    /**
     * @param string $name
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByName(string $name, \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector = null): void;

    /**
     * @param string $text
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByText(string $text, \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector = null): void;

    /**
     * @param string $cssOrXPath css or xpath of the web element (body by default)
     * @param int $offsetX
     * @param int $offsetY
     */
    public function clickWithLeftButton(string $cssOrXPath = null, int $offsetX = null, int $offsetY = null);

    /**
     * @param string $cssOrXPath css or xpath of the web element (body by default)
     * @param int $offsetX
     * @param int $offsetY
     */
    public function clickWithRightButton(string $cssOrXPath = null, int $offsetX = null, int $offsetY = null);

    public function closeTab();

    /**
     * @param string $css
     * @return int
     */
    public function countVisibleByCss(string $css): int;

    /**
     * @param \Codeception\TestInterface $test
     */
    public function debugWebDriverLogs(?TestInterface $test = null);

    /**
     * @param string $name
     */
    public function deleteSessionSnapshot(string $name);

    /**
     * @param string $text
     * @param array|string $selector optional
     */
    public function dontSee(string $text, array|string $selector = null);

    /**
     * @param string $checkboxId
     */
    public function dontSeeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function dontSeeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param string $cookie
     * @param array $params
     */
    public function dontSeeCookie(string $cookie, array $params = []);

    /**
     * @param string $uri
     */
    public function dontSeeCurrentUrlEquals(string $uri);

    /**
     * @param string $uri
     */
    public function dontSeeCurrentUrlMatches(string $uri);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function dontSeeElement(array|string $selector, array $attributes = []);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function dontSeeElementInDOM(array|string $selector, array $attributes = []);

    /**
     * @param string $uri
     */
    public function dontSeeInCurrentUrl(string $uri);

    /**
     * @param string $table
     * @param array $criteria
     */
    public function dontSeeInDatabase(string $table, array $criteria = []);

    /**
     * @param string $field
     * @param string $value
     */
    public function dontSeeInField(string $field, string $value);

    /**
     * @param array|string $formSelector
     * @param array $params
     */
    public function dontSeeInFormFields(array|string $formSelector, array $params);

    /**
     * @param string $text
     */
    public function dontSeeInPageSource(string $text);

    /**
     * @param string $text
     */
    public function dontSeeInPopup(string $text);

    /**
     * @param string $raw
     */
    public function dontSeeInSource(string $raw);

    /**
     * @param string $title
     */
    public function dontSeeInTitle(string $title);

    /**
     * @param string $text
     * @param string $url optional
     */
    public function dontSeeLink(string $text, string $url = null);

    /**
     * @param array|string $selector
     * @param string $optionText
     */
    public function dontSeeOptionIsSelected(array|string $selector, string $optionText);

    /**
     * @param string $cssOrXPath
     */
    public function doubleClick(string $cssOrXPath);

    /**
     * @param string $source (CSS ID or XPath)
     * @param string $target (CSS ID or XPath)
     */
    public function dragAndDrop(string $source, string $target);

    /**
     * @param callable $callable
     */
    public function execute(callable $callable);

    /**
     * @param string $script
     * @param array $arguments
     * @return mixed
     */
    public function executeAsyncJS(string $script, array $arguments = []);

    /**
     * @param \Closure $function
     */
    public function executeInSelenium(Closure $function);

    /**
     * @param string $script
     * @param array $arguments
     * @return mixed
     */
    public function executeJS(string $script, array $arguments = []);

    /**
     * @param string $prediction
     */
    public function expect(string $prediction);

    /**
     * @param string $prediction
     */
    public function expectTo(string $prediction);

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
     * @param string $cssOrXpath
     * @param string $attribute
     * @return mixed
     */
    public function grabAttributeFrom(string $cssOrXpath, string $attribute);

    /**
     * @param string $table
     * @param string $column
     * @param array $criteria
     * @return array
     */
    public function grabColumnFromDatabase(string $table, string $column, array $criteria = []);

    /**
     * @param string $cookie
     * @param array $params
     * @return mixed
     */
    public function grabCookie(string $cookie, array $params = []);

    /**
     * @param string $uri optional
     * @return mixed
     */
    public function grabFromCurrentUrl(string $uri = null);

    /**
     * @param string $table
     * @param string $column
     * @param array $criteria
     * @return mixed
     */
    public function grabFromDatabase(string $table, string $column, array $criteria = []);

    /**
     * @param string $cssOrXpath
     * @param string $attribute
     * @return string[]
     */
    public function grabMultiple(string $cssOrXpath, string $attribute = null);

    /**
     * @param string $table Table name
     * @param array $criteria Search criteria [Optional]
     * @return int
     */
    public function grabNumRecords(string $table, array $criteria = []);

    /**
     * @return string current page source code
     */
    public function grabPageSource();

    /**
     * @param string $serviceId
     * @return object
     */
    public function grabServiceFromContainer(string $serviceId);

    /**
     * @param string $cssOrXPathOrRegex
     * @return mixed
     */
    public function grabTextFrom(string $cssOrXPathOrRegex);

    /**
     * @param string $field
     * @return mixed
     */
    public function grabValueFrom(string $field);

    /**
     * @param string $table
     * @param array $data
     * @return int
     */
    public function haveInDatabase(string $table, array $data);

    /**
     * @param string $name
     * @return mixed
     */
    public function loadSessionSnapshot(string $name);

    /**
     * @param string $name
     */
    public function makeScreenshot(string $name = null);

    public function maximizeWindow();

    public function moveBack();

    public function moveForward();

    /**
     * @param string $cssOrXPath css or xpath of the web element
     * @param int $offsetX
     * @param int $offsetY
     */
    public function moveMouseOver(string $cssOrXPath = null, int $offsetX = null, int $offsetY = null);

    /**
     * @param string $css
     * @param int|null $offsetX
     * @param int|null $offsetY
     */
    public function moveMouseOverByCss(string $css, ?int $offsetX = null, ?int $offsetY = null): void;

    public function openNewTab();

    public function pauseExecution();

    /**
     * @param string $databaseKey
     * @param \Codeception\Util\ActionSequence|array|callable $actions
     */
    public function performInDatabase(string $databaseKey, \Codeception\Util\ActionSequence|array|callable $actions);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param array $actions
     * @param int $timeout
     */
    public function performOn(\Facebook\WebDriver\WebDriverElement $element, array $actions, int $timeout = 10);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param string|string[] $keys
     */
    public function pressKeysByElement(WebDriverElement $element, string|array $keys): void;

    public function reloadPage();

    /**
     * @param string $cookie
     * @param array $params
     * @return mixed
     */
    public function resetCookie(string $cookie, array $params = []);

    /**
     * @param int $width
     * @param int $height
     */
    public function resizeWindow(int $width, int $height);

    /**
     * @param string $name
     * @return mixed
     */
    public function saveSessionSnapshot(string $name);

    /**
     * @param array|string $selector
     * @param int $offsetX
     * @param int $offsetY
     */
    public function scrollTo(array|string $selector, int $offsetX = null, int $offsetY = null);

    /**
     * @param string $text
     * @param array|string $selector optional
     */
    public function see(string $text, array|string $selector = null);

    /**
     * @param string $checkboxId
     */
    public function seeCheckboxIsCheckedById(string $checkboxId): void;

    /**
     * @param string $label
     */
    public function seeCheckboxIsCheckedByLabel(string $label): void;

    /**
     * @param string $cookie
     * @param array $params
     */
    public function seeCookie(string $cookie, array $params = []);

    /**
     * @param string $page
     */
    public function seeCurrentPageEquals(string $page);

    /**
     * @param string $uri
     */
    public function seeCurrentUrlEquals(string $uri);

    /**
     * @param string $uri
     */
    public function seeCurrentUrlMatches(string $uri);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function seeElement(array|string $selector, array $attributes = []);

    /**
     * @param array|string $selector
     * @param array $attributes
     */
    public function seeElementInDOM(array|string $selector, array $attributes = []);

    /**
     * @param string $text
     * @param string $css
     */
    public function seeInCss(string $text, string $css);

    /**
     * @param string $uri
     */
    public function seeInCurrentUrl(string $uri);

    /**
     * @param string $table
     * @param array $criteria
     */
    public function seeInDatabase(string $table, array $criteria = []);

    /**
     * @param string $text
     * @param \Facebook\WebDriver\WebDriverElement $element
     */
    public function seeInElement(string $text, WebDriverElement $element): void;

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
     * @param array|string $formSelector
     * @param array $params
     */
    public function seeInFormFields(array|string $formSelector, array $params);

    /**
     * @param string $text
     */
    public function seeInPageSource(string $text);

    /**
     * @param string $text
     */
    public function seeInPopup(string $text);

    /**
     * @param string $raw
     */
    public function seeInSource(string $raw);

    /**
     * @param string $title
     */
    public function seeInTitle(string $title);

    /**
     * @param string $text
     * @param string $url optional
     */
    public function seeLink(string $text, string $url = null);

    /**
     * @param int $expectedNumber Expected number
     * @param string $table Table name
     * @param array $criteria Search criteria [Optional]
     */
    public function seeNumRecords(int $expectedNumber, string $table, array $criteria = []);

    /**
     * @param array|string $selector
     * @param mixed $expected int or int[]
     */
    public function seeNumberOfElements(array|string $selector, mixed $expected);

    /**
     * @param array|string $selector
     * @param mixed $expected int or int[]
     */
    public function seeNumberOfElementsInDOM(array|string $selector, mixed $expected);

    /**
     * @param array|string $selector
     * @param string $optionText
     */
    public function seeOptionIsSelected(array|string $selector, string $optionText);

    /**
     * @param array|string $select
     * @param string $option
     */
    public function selectOption(array|string $select, string $option);

    /**
     * @param string $selectCss
     * @param string $optionValue
     */
    public function selectOptionByCssAndValue(string $selectCss, string $optionValue);

    /**
     * @param string $cookie
     * @param string $value
     * @param array $params
     * @param mixed $showDebug
     * @return mixed
     */
    public function setCookie(string $cookie, string $value, array $params = [], mixed $showDebug = true);

    /**
     * @param array|string $selector
     * @param array $params
     * @param string $button
     */
    public function submitForm(array|string $selector, array $params, string $button = null);

    /**
     * @param string|null $name
     */
    public function switchToIFrame(?string $name = null);

    public function switchToLastOpenedWindow();

    /**
     * @param int $offset 1
     */
    public function switchToNextTab(int $offset = 1);

    /**
     * @param int $offset 1
     */
    public function switchToPreviousTab(int $offset = 1);

    /**
     * @param string|null $name
     */
    public function switchToWindow(?string $name = null);

    /**
     * @param array $keys
     */
    public function typeInPopup(array $keys);

    /**
     * @param string $option
     */
    public function uncheckOption(string $option);

    /**
     * @param array|string $select
     * @param string $option
     */
    public function unselectOption(array|string $select, string $option);

    /**
     * @param string $table
     * @param array $data
     * @param array $criteria
     */
    public function updateInDatabase(string $table, array $data, array $criteria = []);

    /**
     * @param int|float $timeout secs
     */
    public function wait(int|float $timeout);

    /**
     * @param int $timeout
     */
    public function waitForAjax(int $timeout = null);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param int $timeout seconds
     */
    public function waitForElement(\Facebook\WebDriver\WebDriverElement $element, int $timeout = null);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param \Closure $callback
     * @param int $timeout seconds
     */
    public function waitForElementChange(\Facebook\WebDriver\WebDriverElement $element, Closure $callback, int $timeout = null);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param int $timeout seconds
     */
    public function waitForElementClickable(\Facebook\WebDriver\WebDriverElement $element, int $timeout = null);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param int $timeout seconds
     */
    public function waitForElementNotVisible(\Facebook\WebDriver\WebDriverElement $element, int $timeout = null);

    /**
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param int $timeout seconds
     */
    public function waitForElementVisible(\Facebook\WebDriver\WebDriverElement $element, int $timeout = null);

    /**
     * @param string $script
     * @param int $timeout seconds
     */
    public function waitForJS(string $script, int $timeout = null);

    /**
     * @param string $text
     * @param int $timeout seconds
     * @param string $selector optional
     */
    public function waitForText(string $text, int $timeout = null, string $selector = null);

    /**
     * @param string $text
     */
    public function wantTo(string $text);

    /**
     * @param string $text
     */
    public function wantToTest(string $text);
}
