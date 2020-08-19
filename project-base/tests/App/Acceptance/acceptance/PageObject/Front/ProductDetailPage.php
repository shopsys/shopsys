<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class ProductDetailPage extends AbstractPage
{
    protected const PRODUCT_DETAIL_QUANTITY_INPUT = '.js-product-detail-main-add-to-cart-wrapper input[name="add_product_form[quantity]"]';
    protected const PRODUCT_DETAIL_MAIN_WRAPPER = '.js-product-detail-main-add-to-cart-wrapper';

    /**
     * @param int $quantity
     */
    public function addProductIntoCart($quantity = 1)
    {
        $this->tester->fillFieldByCss(
            self::PRODUCT_DETAIL_QUANTITY_INPUT,
            (string)$quantity
        );
        $this->tester->clickByTranslationFrontend('Add to cart', 'messages', [], WebDriverBy::cssSelector(self::PRODUCT_DETAIL_MAIN_WRAPPER));
        $this->tester->waitForAjax();
        $this->tester->wait(1); // animation of popup window
    }
}
