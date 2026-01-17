<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Module;

use Codeception\Module\WebDriver;
use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverKeys;
use Override;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\Test\Codeception\Exception\DeprecatedMethodException;

class StrictWebDriver extends WebDriver
{
    private const WAIT_AFTER_CLICK_MICROSECONDS = 50000;

    /**
     * @param string[] $alternatives
     */
    private function getDeprecatedMethodExceptionMessage(array $alternatives): string
    {
        $messageWithAlternativesPlaceholder = 'This method is deprecated because it uses fuzzy locators. '
            . 'Use one of strict alternatives instead: %s. Or implement new method with strict locator. See ' . self::class;

        return sprintf(
            $messageWithAlternativesPlaceholder,
            implode(', ', $alternatives),
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function match($page, $selector, $throwMalformed = true): array
    {
        if (!is_array($selector) && !$selector instanceof WebDriverBy) {
            $message = 'Using match() with fuzzy locator is slow. '
                . 'You should implement new method with strict locator. See ' . self::class;

            throw new DeprecatedMethodException($message);
        }

        return parent::match($page, $selector, $throwMalformed);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function findFields($selector): array
    {
        if (!is_array($selector) && !$selector instanceof WebDriverElement) {
            $message = 'Using findFields() with fuzzy locator is slow. '
                . 'You should implement new method with strict locator. See ' . self::class;

            throw new DeprecatedMethodException($message);
        }

        return parent::findFields($selector);
    }

    /**
     * @internal This method prevents developers from using parent method
     * @param mixed $link
     * @param mixed|null $context
     */
    #[Override]
    public function click($link, $context = null): void
    {
        $strictAlternatives = [
            'clickBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    private function clickAndWaitByElement(WebDriverElement $element): void
    {
        $this->clickByElement($element);

        // workaround for race conditions when WebDriver tries to interact with page before click was processed
        usleep(self::WAIT_AFTER_CLICK_MICROSECONDS);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByText(string $text, $contextSelector = null): void
    {
        $locateBy = $this->getWebDriverByText($text);
        $clickable = $this->getElementBySelectorAndContext($locateBy, $contextSelector);
        $clickable->click();

        // workaround for race conditions when WebDriver tries to interact with page before click was processed
        usleep(self::WAIT_AFTER_CLICK_MICROSECONDS);
    }

    private function getWebDriverByText(string $text): WebDriverBy
    {
        $locator = Crawler::xpathLiteral(trim($text));
        $xpath = Locator::combine(
            ".//a[normalize-space(.)={$locator}]",
            ".//button[normalize-space(.)={$locator}]",
            ".//a/img[normalize-space(@alt)={$locator}]/ancestor::a",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][normalize-space(@value)={$locator}]",
        );

        return WebDriverBy::xpath($xpath);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     * @return \Facebook\WebDriver\Remote\RemoteWebElement|\Facebook\WebDriver\WebDriverElement
     */
    private function getElementBySelectorAndContext(WebDriverBy $locateBy, $contextSelector = null): WebDriverElement
    {
        if ($contextSelector instanceof WebDriverBy) {
            return $this->webDriver->findElement($contextSelector)->findElement($locateBy);
        }

        if ($contextSelector instanceof WebDriverElement) {
            return $contextSelector->findElement($locateBy);
        }

        return $this->webDriver->findElement($locateBy);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByName(string $name, $contextSelector = null): void
    {
        $element = $this->getElementBySelectorAndContext(WebDriverBy::name($name), $contextSelector);

        $this->clickAndWaitByElement($element);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByCss(string $css, $contextSelector = null): void
    {
        $element = $this->getElementBySelectorAndContext(WebDriverBy::cssSelector($css), $contextSelector);

        $this->clickAndWaitByElement($element);
    }

    public function clickByElement(WebDriverElement $element): WebDriverElement
    {
        return $element->click();
    }

    /**
     * @internal This method prevents developers from using parent method
     * @param mixed $field
     * @param mixed $value
     */
    #[Override]
    public function fillField($field, $value): void
    {
        $strictAlternatives = [
            'fillFieldBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    public function fillFieldByElement(WebDriverElement $element, string $value): void
    {
        $this->clearElement($element);
        $element->sendKeys($value);
    }

    private function clearElement(WebDriverElement $element): void
    {
        $element->clear();

        while ($element->getAttribute('value') !== '') {
            $element->sendKeys(WebDriverKeys::BACKSPACE);
            $element->sendKeys(WebDriverKeys::DELETE);
        }
    }

    public function fillFieldByName(string $fieldName, string $value): void
    {
        $element = $this->webDriver->findElement(WebDriverBy::name($fieldName));
        $this->fillFieldByElement($element, $value);
    }

    public function fillFieldByCss(string $css, string $value): void
    {
        $element = $this->webDriver->findElement(WebDriverBy::cssSelector($css));
        $this->fillFieldByElement($element, $value);
    }

    public function findElementByCss(string $css): WebDriverElement
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector($css));
    }

    public function scrollToElement(WebDriverElement $webDriverElement): void
    {
        $webDriverElement->getLocationOnScreenOnceScrolledIntoView();
    }

    public function seeInCss(string $text, string $css): void
    {
        $element = $this->webDriver->findElement(WebDriverBy::cssSelector($css));
        $this->seeInElement($text, $element);
    }

    public function seeInElement(string $text, WebDriverElement $element): void
    {
        $this->assertStringContainsString($text, $element->getText());
    }

    /**
     * @internal This method prevents developers from using parent method
     * @param mixed $checkbox
     */
    #[Override]
    public function seeCheckboxIsChecked($checkbox): void
    {
        $strictAlternatives = [
            'seeCheckboxIsCheckedBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    public function seeCheckboxIsCheckedById(string $checkboxId): void
    {
        $xpath = $this->getCheckboxIdXpathSelector($checkboxId);
        $element = $this->webDriver->findElement(WebDriverBy::xpath($xpath));

        $this->assertTrue($element->isSelected());
    }

    public function seeCheckboxIsCheckedByLabel(string $label): void
    {
        /*
         * XPath explanation:
         *
         * First combine() argument:
         * Search for <input type="checkbox" id=myCheckboxId>,
         * where myCheckboxId is value of "for" attribute of <label for=myCheckboxId>$label</label>.
         *
         * Second combine() argument:
         * Search for <label>$label</label>. Inside of it search for <input type="checkbox">.
         */
        $xpath = Locator::combine(
            './/*[self::input[@type="checkbox"]][./@id = //label[contains(normalize-space(string(.)), "' . $label . '")]/@for]',
            './/label[contains(normalize-space(string(.)), "' . $label . '")]//.//*[self::input[@type="checkbox"]]',
        );

        $element = $this->webDriver->findElement(WebDriverBy::xpath($xpath));

        $this->assertTrue($element->isSelected());
    }

    /**
     * @internal This method is used to prevent users from using parent method
     * @param mixed $checkbox
     */
    #[Override]
    public function dontSeeCheckboxIsChecked($checkbox): void
    {
        $strictAlternatives = [
            'dontSeeCheckboxIsCheckedBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    public function dontSeeCheckboxIsCheckedById(string $checkboxId): void
    {
        $xpath = $this->getCheckboxIdXpathSelector($checkboxId);
        $element = $this->webDriver->findElement(WebDriverBy::xpath($xpath));

        $this->assertFalse($element->isSelected());
    }

    public function dontSeeCheckboxIsCheckedByLabel(string $label): void
    {
        /*
         * XPath explanation:
         *
         * First combine() argument:
         * Search for <input type="checkbox" id=myCheckboxId>,
         * where myCheckboxId is value of "for" attribute of <label for=myCheckboxId>$label</label>.
         *
         * Second combine() argument:
         * Search for <label>$label</label>. Inside of it search for <input type="checkbox">.
         */
        $xpath = Locator::combine(
            './/*[self::input[@type="checkbox"]][./@id = //label[contains(normalize-space(string(.)), "' . $label . '")]/@for]',
            './/label[contains(normalize-space(string(.)), "' . $label . '")]//.//*[self::input[@type="checkbox"]]',
        );

        $element = $this->webDriver->findElement(WebDriverBy::xpath($xpath));

        $this->assertFalse($element->isSelected());
    }

    /**
     * @internal This method prevents developers from using parent method
     * @param mixed $option
     */
    #[Override]
    public function checkOption($option): void
    {
        $strictAlternatives = [
            'checkOptionBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    public function checkElement(WebDriverElement $element): void
    {
        if ($element->isSelected()) {
            return;
        }
        $element->click();
    }

    public function checkOptionById(string $optionId): void
    {
        $locator = Crawler::xpathLiteral(trim($optionId));
        $xpath = './/input[@type = "checkbox" or @type = "radio"][./@id = ' . $locator . ']';

        $element = $this->webDriver->findElement(WebDriverBy::xpath($xpath));

        $this->checkElement($element);
    }

    public function checkOptionByLabel(string $label): void
    {
        /*
         * XPath explanation:
         *
         * First combine() argument:
         * Search for <input type="checkbox" id=myCheckboxId>,
         * where myCheckboxId is value of "for" attribute of <label for=myCheckboxId>$label</label>.
         *
         * Second combine() argument:
         * Search for <label>$label</label>. Inside of it search for <input type="checkbox">.
         */
        $xpath = Locator::combine(
            './/*[self::input[@type="checkbox"]][./@id = //label[contains(normalize-space(string(.)), "' . $label . '")]/@for]',
            './/label[contains(normalize-space(string(.)), "' . $label . '")]//.//*[self::input[@type="checkbox"]]',
        );

        $element = $this->webDriver->findElement(WebDriverBy::xpath($xpath));

        $this->checkElement($element);
    }

    public function selectOptionByCssAndValue(string $selectCss, string $optionValue): void
    {
        $select = $this->webDriver->findElement(WebDriverBy::cssSelector($selectCss));

        parent::selectOption(['css' => $selectCss], $optionValue);
    }

    public function selectOptionInSelect2ByCssAndValue(string $selectCss, string $optionValue): void
    {
        $this->executeJS("$('" . $selectCss . "').val('" . $optionValue . "');$('" . $selectCss . "').trigger('change');");
    }

    public function countVisibleByCss(string $css): int
    {
        $elements = parent::matchVisible(['css' => $css]);

        return count($elements);
    }

    /**
     * @internal This method prevents developers from using parent method
     * @param mixed $field
     * @param mixed $value
     */
    #[Override]
    public function seeInField($field, $value): void
    {
        $strictAlternatives = [
            'seeInFieldBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    public function seeInFieldByName(string $value, string $fieldName): void
    {
        $element = $this->webDriver->findElement(WebDriverBy::name($fieldName));

        $this->seeInFieldByElement($value, $element);
    }

    public function seeInFieldByElement(string $value, WebDriverElement $element): void
    {
        // @phpstan-ignore-next-line
        parent::seeInField($element, $value);
    }

    /**
     * @internal This method prevents developers from using parent method
     * @param mixed $element
     * @param mixed $chars
     */
    #[Override]
    public function pressKey($element, ...$chars): void
    {
        $strictAlternatives = [
            'pressKeysBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);

        throw new DeprecatedMethodException($message);
    }

    /**
     * Examples:
     * $I->pressKeysByElement($element, 'hello'); // hello
     * $I->pressKeysByElement($element, ['n', 'e', 'w']); // new
     * $I->pressKeysByElement($element, [[\Facebook\WebDriver\WebDriverKeys, 'day'], 1]); // DAY1
     *
     * For available keys:
     *
     * @see \Facebook\WebDriver\WebDriverKeys
     * @param string|string[] $keys
     */
    public function pressKeysByElement(WebDriverElement $element, $keys): void
    {
        $element->sendKeys($keys);
    }

    /**
     * @param string $text
     * @param array $nodes
     * @param mixed $selector
     */
    #[Override]
    protected function assertNodesContain($text, $nodes, $selector = null): void
    {
        $message = Locator::humanReadableString($selector);

        parent::assertNodesContain($text, $nodes, $message);
    }

    /**
     * @param string $text
     * @param array $nodes
     * @param mixed $selector
     */
    #[Override]
    protected function assertNodesNotContain($text, $nodes, $selector = null): void
    {
        $message = Locator::humanReadableString($selector);

        parent::assertNodesNotContain($text, $nodes, $message);
    }

    protected function getCheckboxIdXpathSelector(string $checkboxId): string
    {
        $locator = Crawler::xpathLiteral(trim($checkboxId));

        return './/input[@type = "checkbox"][./@id = ' . $locator . ']';
    }
}
