<?php

declare(strict_types=1);

namespace App\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade as BaseCartWatcherFacade;

class CartWatcherFacade extends BaseCartWatcherFacade
{
    /**
     * @return string
     */
    protected function getMessageForNoLongerAvailableExistingProduct(): string
    {
        return t('Product <strong>{{ name }}</strong> you had in cart is no longer available. Please check your order.');
    }

    /**
     * @return string
     */
    protected function getMessageForNoLongerAvailableProduct(): string
    {
        return t('Product you had in cart is no longer in available. Please check your order.');
    }

    /**
     * @return string
     */
    protected function getMessageForChangedProduct(): string
    {
        return t('The price of the product <strong>{{ name }}</strong> you have in cart has changed. Please, check your order.');
    }
}
