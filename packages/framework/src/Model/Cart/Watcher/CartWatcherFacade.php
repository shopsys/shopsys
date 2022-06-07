<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

abstract class CartWatcherFacade
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
     * @return string
     */
    abstract protected function getMessageForNoLongerAvailableExistingProduct(): string;

    /**
     * @return string
     */
    abstract protected function getMessageForNoLongerAvailableProduct(): string;

    /**
     * @return string
     */
    abstract protected function getMessageForChangedProduct(): string;
}
