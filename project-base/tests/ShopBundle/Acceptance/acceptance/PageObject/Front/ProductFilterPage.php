<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;
use Tests\ShopBundle\Test\Codeception\Module\StrictWebDriver;

class ProductFilterPage extends AbstractPage
{
    // Product filter waits for more requests before evaluation
    const PRE_EVALUATION_WAIT = 2;

    public function __construct(StrictWebDriver $strictWebDriver, AcceptanceTester $tester)
    {
        parent::__construct($strictWebDriver, $tester);
    }
    
    public function setMinimalPrice(string $price): void
    {
        $this->tester->fillFieldByCss('#product_filter_form_minimalPrice', $price . WebDriverKeys::ENTER);
        $this->waitForFilter();
    }
    
    public function setMaximalPrice(string $price): void
    {
        $this->tester->fillFieldByCss('#product_filter_form_maximalPrice', $price . WebDriverKeys::ENTER);
        $this->waitForFilter();
    }
    
    public function filterByBrand(string $label): void
    {
        $this->tester->checkOptionByLabel($label);
        $this->waitForFilter();
    }
    
    public function filterByParameter(string $parameterLabel, string $valueLabel): void
    {
        $parameterElement = $this->findParameterElementByLabel($parameterLabel);
        $labelElement = $this->getLabelElementByParameterValueText($parameterElement, $valueLabel);
        $labelElement->click();
        $this->waitForFilter();
    }

    private function waitForFilter(): void
    {
        $this->tester->wait(self::PRE_EVALUATION_WAIT);
        $this->tester->waitForAjax();
    }
    
    private function findParameterElementByLabel(string $parameterLabel): \Facebook\WebDriver\WebDriverElement
    {
        $parameterItems = $this->webDriver->findElements(
            WebDriverBy::cssSelector('#product_filter_form_parameters .js-product-filter-parameter')
        );

        foreach ($parameterItems as $item) {
            try {
                $itemLabel = $item->findElement(WebDriverBy::cssSelector('.js-product-filter-parameter-label'));

                if (stripos($itemLabel->getText(), $parameterLabel) !== false) {
                    return $item;
                }
            } catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
                continue;
            }
        }

        $message = 'Unable to find parameter with label "' . $parameterLabel . '" in product filter.';
        throw new \Facebook\WebDriver\Exception\NoSuchElementException($message);
    }
    
    private function getLabelElementByParameterValueText(\Facebook\WebDriver\WebDriverElement $parameterElement, string $parameterValueText): \Facebook\WebDriver\WebDriverElement
    {
        $labelElements = $parameterElement->findElements(WebDriverBy::cssSelector('.js-product-filter-parameter-value'));

        foreach ($labelElements as $labelElement) {
            try {
                if (stripos($labelElement->getText(), $parameterValueText) !== false) {
                    return $labelElement;
                }
            } catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
                continue;
            }
        }

        $message = 'Unable to find parameter value with label "' . $parameterValueText . '" in product filter.';
        throw new \Facebook\WebDriver\Exception\NoSuchElementException($message);
    }
}
