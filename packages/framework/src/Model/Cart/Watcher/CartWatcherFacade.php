<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

/**
 * @deprecated Class will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.
 */
class CartWatcherFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher
     */
    protected $cartWatcher;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    protected $flashBag;

    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        FlashBagInterface $flashBag,
        EntityManagerInterface $em,
        CartWatcher $cartWatcher,
        CurrentCustomerUser $currentCustomerUser,
        Environment $twigEnvironment
    ) {
        if (static::class === self::class) {
            DeprecationHelper::triggerAbstractClass(self::class);
        }

        $this->flashBag = $flashBag;
        $this->em = $em;
        $this->cartWatcher = $cartWatcher;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function checkCartModifications(Cart $cart)
    {
        $this->checkNotListableItems($cart);
        $this->checkModifiedPrices($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkModifiedPrices(Cart $cart)
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        $messageTemplate = $this->twigEnvironment->createTemplate(
            $this->getMessageForChangedProduct()
        );

        foreach ($modifiedItems as $cartItem) {
            $this->flashBag->add(FlashMessage::KEY_INFO, $messageTemplate->render(['name' => $cartItem->getName()]));
        }

        if (count($modifiedItems) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkNotListableItems(Cart $cart)
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        $toFlush = [];

        $messageTemplate = $this->twigEnvironment->createTemplate(
            $this->getMessageForNoLongerAvailableExistingProduct()
        );

        foreach ($notVisibleItems as $cartItem) {
            try {
                $productName = $cartItem->getName();
                $this->flashBag->add(FlashMessage::KEY_ERROR, $messageTemplate->render(['name' => $productName]));
            } catch (ProductNotFoundException $e) {
                $this->flashBag->add(
                    FlashMessage::KEY_ERROR,
                    $this->getMessageForNoLongerAvailableProduct()
                );
            }

            $cart->removeItemById($cartItem->getId());
            $this->em->remove($cartItem);
            $toFlush[] = $cartItem;
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    protected function getMessageForNoLongerAvailableExistingProduct(): string
    {
        DeprecationHelper::triggerAbstractMethod(__METHOD__);

        return t('Product <strong>{{ name }}</strong> you had in cart is no longer available. Please check your order.');
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    protected function getMessageForNoLongerAvailableProduct(): string
    {
        DeprecationHelper::triggerAbstractMethod(__METHOD__);

        return t('Product you had in cart is no longer in available. Please check your order.');
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    protected function getMessageForChangedProduct(): string
    {
        DeprecationHelper::triggerAbstractMethod(__METHOD__);

        return t('The price of the product <strong>{{ name }}</strong> you have in cart has changed. Please, check your order.');
    }
}
