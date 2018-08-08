<?php

namespace Tests\ShopBundle\Test\Codeception\Module;

use Codeception\Module\WebDriver;
use Codeception\Util\Locator;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\DomCrawler\Crawler;

class StrictWebDriver extends WebDriver
{
    const WAIT_AFTER_CLICK_MICROSECONDS = 50000;

    /**
     * @param string[] $alternatives
     */
    private function getDeprecatedMethodExceptionMessage(array $alternatives): string
    {
        $messageWithAlternativesPlaceholder = 'This method is deprecated because it uses fuzzy locators. '
            . 'Use one of strict alternatives instead: %s. Or implement new method with strict locator. See ' . self::class;

        return sprintf(
            $messageWithAlternativesPlaceholder,
            implode(', ', $alternatives)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function match($page, $selector, $throwMalformed = true)
    {
        if (!is_array($selector) && !$selector instanceof WebDriverBy) {
            $message = 'Using match() with fuzzy locator is slow. '
                . 'You should implement new method with strict locator. See ' . self::class;
            throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
        }
        return parent::match($page, $selector, $throwMalformed);
    }

    /**
     * {@inheritDoc}
     */
    protected function findFields($selector)
    {
        if (!is_array($selector) && !$selector instanceof WebDriverElement) {
            $message = 'Using findFields() with fuzzy locator is slow. '
                . 'You should implement new method with strict locator. See ' . self::class;
            throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
        }
        return parent::findFields($selector);
    }

    /**
     * @deprecated
     */
    public function click($link, $context = null): void
    {
        $strictAlternatives = [
            'clickBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    /**
     * @see click()
     */
    private function clickAndWait($link, $context = null): void
    {
        parent::click($link, $context);

        // workaround for race conditions when WebDriver tries to interact with page before click was processed
        usleep(self::WAIT_AFTER_CLICK_MICROSECONDS);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByText(string $text, $contextSelector = null): void
    {
        $locator = Crawler::xpathLiteral(trim($text));

        $xpath = Locator::combine(
            './/a[normalize-space(.)=' . $locator . ']',
            './/button[normalize-space(.)=' . $locator . ']',
            './/a/img[normalize-space(@alt)=' . $locator . ']/ancestor::a',
            './/input[./@type = "submit" or ./@type = "image" or ./@type = "button"][normalize-space(@value)=' . $locator . ']'
        );

        $this->clickAndWait(['xpath' => $xpath], $contextSelector);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByName(string $name, $contextSelector = null): void
    {
        $locator = Crawler::xpathLiteral(trim($name));

        $xpath = Locator::combine(
            './/input[./@type = "submit" or ./@type = "image" or ./@type = "button"][./@name = ' . $locator . ']',
            './/button[./@name = ' . $locator . ']'
        );

        $this->clickAndWait(['xpath' => $xpath], $contextSelector);
    }

    public function clickByCss(string $css): void
    {
        $this->clickAndWait(['css' => $css]);
    }

    public function clickByElement(WebDriverElement $element): \Facebook\WebDriver\WebDriverElement
    {
        $element->click();
    }

    /**
     * @deprecated
     */
    public function fillField($field, $value): void
    {
        $strictAlternatives = [
            'fillFieldBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    public function fillFieldByElement(WebDriverElement $element, string $value): void
    {
        $element->clear();
        $element->sendKeys($value);
    }

    public function fillFieldByName(string $fieldName, string $value): void
    {
        $locator = Crawler::xpathLiteral(trim($fieldName));
        $xpath = './/*[self::input | self::textarea | self::select][@name = ' . $locator . ']';

        parent::fillField(['xpath' => $xpath], $value);
    }

    public function fillFieldByCss(string $css, string $value): void
    {
        parent::fillField(['css' => $css], $value);
    }

    public function seeInCss(string $text, string $css): void
    {
        parent::see($text, ['css' => $css]);
    }

    public function seeInElement(string $text, WebDriverElement $element): void
    {
        $this->assertContains($text, $element->getText());
    }

    /**
     * @deprecated
     */
    public function seeCheckboxIsChecked($checkbox): void
    {
        $strictAlternatives = [
            'seeCheckboxIsCheckedBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    public function seeCheckboxIsCheckedById(string $checkboxId): void
    {
        $locator = Crawler::xpathLiteral(trim($checkboxId));
        $xpath = './/input[@type = "checkbox"][./@id = ' . $locator . ']';

        parent::seeCheckboxIsChecked(['xpath' => $xpath]);
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
            './/label[contains(normalize-space(string(.)), "' . $label . '")]//.//*[self::input[@type="checkbox"]]'
        );

        parent::seeCheckboxIsChecked(['xpath' => $xpath]);
    }

    /**
     * @deprecated
     */
    public function dontSeeCheckboxIsChecked($checkbox): void
    {
        $strictAlternatives = [
            'dontSeeCheckboxIsCheckedBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    public function dontSeeCheckboxIsCheckedById(string $checkboxId): void
    {
        $locator = Crawler::xpathLiteral(trim($checkboxId));
        $xpath = './/input[@type = "checkbox"][./@id = ' . $locator . ']';

        parent::dontSeeCheckboxIsChecked(['xpath' => $xpath]);
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
            './/label[contains(normalize-space(string(.)), "' . $label . '")]//.//*[self::input[@type="checkbox"]]'
        );

        parent::dontSeeCheckboxIsChecked(['xpath' => $xpath]);
    }

    /**
     * @deprecated
     */
    public function checkOption($option): void
    {
        $strictAlternatives = [
            'checkOptionBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    public function checkOptionById(string $optionId): void
    {
        $locator = Crawler::xpathLiteral(trim($optionId));
        $xpath = './/input[@type = "checkbox" or @type = "radio"][./@id = ' . $locator . ']';

        parent::checkOption(['xpath' => $xpath]);
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
            './/label[contains(normalize-space(string(.)), "' . $label . '")]//.//*[self::input[@type="checkbox"]]'
        );
        parent::checkOption(['xpath' => $xpath]);
    }

    public function selectOptionByCssAndValue(string $selectCss, string $optionValue): void
    {
        parent::selectOption(['css' => $selectCss], $optionValue);
    }

    public function countVisibleByCss(string $css): int
    {
        $elements = parent::matchVisible(['css' => $css]);

        return count($elements);
    }

    /**
     * @deprecated
     */
    public function seeInField($field, $value): void
    {
        $strictAlternatives = [
            'seeInFieldBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    public function seeInFieldByName(string $value, string $fieldName): void
    {
        $locator = Crawler::xpathLiteral(trim($fieldName));
        $xpath = './/*[self::input | self::textarea | self::select][@name = ' . $locator . ']';

        parent::seeInField(['xpath' => $xpath], $value);
    }

    public function seeInFieldByElement(string $value, WebDriverElement $element): void
    {
        parent::seeInField($element, $value);
    }

    public function moveMouseOverByCss(string $css, ?int $offsetX = null, ?int $offsetY = null): void
    {
        parent::moveMouseOver(['css' => $css], $offsetX, $offsetY);
    }

    /**
     * @deprecated
     */
    public function pressKey($element, $char): void
    {
        $strictAlternatives = [
            'pressKeysBy*',
        ];
        $message = $this->getDeprecatedMethodExceptionMessage($strictAlternatives);
        throw new \Tests\ShopBundle\Test\Codeception\Exception\DeprecatedMethodException($message);
    }

    /**
     * Examples:
     * $I->pressKeysByElement($element, 'hello'); // hello
     * $I->pressKeysByElement($element, ['n', 'e', 'w']); // new
     * $I->pressKeysByElement($element, [[\Facebook\WebDriver\WebDriverKeys, 'day'], 1]); // DAY1
     *
     * For available keys:
     * @see \Facebook\WebDriver\WebDriverKeys
     *
     * @param string|string[] $keys
     */
    public function pressKeysByElement(WebDriverElement $element, $keys): void
    {
        $element->sendKeys($keys);
    }

    /**
     * @param mixed $selector
     */
    protected function assertNodesContain(string $text, array $nodes, $selector = null): void
    {
        $message = Locator::humanReadableString($selector);
        parent::assertNodesContain($text, $nodes, $message);
    }

    /**
     * @param mixed $selector
     */
    protected function assertNodesNotContain(string $text, array $nodes, $selector = null): void
    {
        $message = Locator::humanReadableString($selector);
        parent::assertNodesNotContain($text, $nodes, $message);
    }
}
