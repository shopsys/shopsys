<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\App\Test\Codeception\AcceptanceTester;
use Tests\App\Test\Codeception\Module\StrictWebDriver;
use Tests\FrameworkBundle\Test\Codeception\FrontCheckbox;

class ProductFilterPage extends AbstractPage
{
    // Product filter waits for more requests before evaluation
    private const PRE_EVALUATION_WAIT = 2;

    /**
     * @param \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver
     * @param \Tests\App\Test\Codeception\AcceptanceTester $tester
     */
    public function __construct(StrictWebDriver $strictWebDriver, AcceptanceTester $tester)
    {
        parent::__construct($strictWebDriver, $tester);
    }

    /**
     * @param string $price
     */
    public function setMinimalPrice(string $price): void
    {
        $convertedPrice = $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($price);
        $this->tester->fillFieldByCss('#product_filter_form_minimalPrice', $convertedPrice . WebDriverKeys::ENTER);
        $this->waitForFilter();
    }

    /**
     * @param string $price
     */
    public function setMaximalPrice(string $price): void
    {
        $convertedPrice = $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($price);
        $this->tester->fillFieldByCss('#product_filter_form_maximalPrice', $convertedPrice . WebDriverKeys::ENTER);
        $this->waitForFilter();
    }

    /**
     * @param int $brandPosition
     */
    public function filterByBrand(int $brandPosition): void
    {
        $frontCheckboxClicker = FrontCheckbox::createByCss(
            $this->tester,
            '#product_filter_form_brands_' . $brandPosition
        );
        $frontCheckboxClicker->check();
        $this->waitForFilter();
    }

    /**
     * @param string $parameterLabel
     * @param string $valueLabel
     */
    public function filterByParameter(string $parameterLabel, string $valueLabel): void
    {
        $parameterElement = $this->findParameterElementByLabel($parameterLabel);
        $labelElement = $this->getLabelElementByParameterValueText($parameterElement, $valueLabel);
        $this->tester->clickByElement($labelElement);
        $this->waitForFilter();
    }

    private function waitForFilter(): void
    {
        $this->tester->wait(self::PRE_EVALUATION_WAIT);
        $this->tester->waitForAjax();
    }

    /**
     * @param string $parameterLabel
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function findParameterElementByLabel(string $parameterLabel): \Facebook\WebDriver\WebDriverElement
    {
        $translatedParameterLabel = t($parameterLabel, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $parameterItems = $this->webDriver->findElements(
            WebDriverBy::cssSelector('#product_filter_form_parameters .test-product-filter-parameter')
        );

        foreach ($parameterItems as $item) {
            try {
                $itemLabel = $item->findElement(WebDriverBy::cssSelector('.test-product-filter-parameter-label'));

                if (mb_stripos($itemLabel->getText(), $translatedParameterLabel) !== false) {
                    return $item;
                }
            } catch (NoSuchElementException $ex) {
                continue;
            }
        }

        $message = sprintf(
            'Unable to find parameter with label "%s" (translated to "%s") in product filter.',
            $parameterLabel,
            $translatedParameterLabel
        );
        throw new NoSuchElementException($message);
    }

    /**
     * @param \Facebook\WebDriver\WebDriverElement $parameterElement
     * @param string $parameterValueText
     * @return \Facebook\WebDriver\WebDriverElement
     */
    private function getLabelElementByParameterValueText(\Facebook\WebDriver\WebDriverElement $parameterElement, string $parameterValueText): \Facebook\WebDriver\WebDriverElement
    {
        $translatedParameterValueText = t($parameterValueText, [], 'dataFixtures', $this->tester->getFrontendLocale());
        $parameterValueDivs = $parameterElement->findElements(
            WebDriverBy::cssSelector('.test-product-filter-parameter-value')
        );

        foreach ($parameterValueDivs as $parameterValueDiv) {
            try {
                $labelElement = $parameterValueDiv->findElement(WebDriverBy::cssSelector('label'));
                if (mb_stripos($labelElement->getText(), $translatedParameterValueText) !== false) {
                    return $labelElement;
                }
            } catch (NoSuchElementException $ex) {
                continue;
            }
        }

        $message = sprintf(
            'Unable to find parameter value with label "%s" (translated to %s) in product filter.',
            $parameterValueText,
            $translatedParameterValueText
        );
        throw new NoSuchElementException($message);
    }
}
