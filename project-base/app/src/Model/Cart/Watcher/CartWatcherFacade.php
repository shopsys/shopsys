<?php

declare(strict_types=1);

namespace App\Model\Cart\Watcher;

use App\Component\Deprecation\DeprecatedMethodException;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade as BaseCartWatcherFacade;

/**
 * @deprecated the original framework class requires FlashBagInterface in the constructor which causes unnecessary session start.
 * Since we use FE API with JS storefront, we do not need the original Twig implementation anymore,
 * however, we are not able to get rid of it completely because it is required in Shopsys\FrameworkBundle\Model\Cart\CartFacade
 * @see \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade
 * @see https://github.com/shopsys/shopsys/pull/2497
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class CartWatcherFacade extends BaseCartWatcherFacade
{
    public function __construct()
    {
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @deprecated see the class description
     * @see \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade
     */
    public function checkCartModifications(Cart $cart): void
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @deprecated see the class description
     * @see \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade
     */
    protected function checkModifiedPrices(Cart $cart): void
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @deprecated see the class description
     * @see \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade
     */
    protected function checkNotListableItems(Cart $cart): void
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @return string
     * @deprecated see the class description
     */
    protected function getMessageForNoLongerAvailableExistingProduct(): string
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @return string
     * @deprecated see the class description
     */
    protected function getMessageForNoLongerAvailableProduct(): string
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @return string
     * @deprecated see the class description
     */
    protected function getMessageForChangedProduct(): string
    {
        throw new DeprecatedMethodException();
    }
}
