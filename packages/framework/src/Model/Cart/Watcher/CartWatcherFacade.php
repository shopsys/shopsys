<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

abstract class CartWatcherFacade
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly EntityManagerInterface $em,
        protected readonly CartWatcher $cartWatcher,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Environment $twigEnvironment,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function checkCartModifications(Cart $cart): void
    {
        $this->checkNotListableItems($cart);
        $this->checkModifiedPrices($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkModifiedPrices(Cart $cart): void
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        $messageTemplate = $this->twigEnvironment->createTemplate(
            $this->getMessageForChangedProduct(),
        );

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag */
        $flashBag = $this->requestStack->getSession()->getBag('flashes');

        foreach ($modifiedItems as $cartItem) {
            $flashBag->add(FlashMessage::KEY_INFO, $messageTemplate->render(['name' => $cartItem->getName()]));
        }

        if (count($modifiedItems) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkNotListableItems(Cart $cart): void
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        $toFlush = [];

        $messageTemplate = $this->twigEnvironment->createTemplate(
            $this->getMessageForNoLongerAvailableExistingProduct(),
        );

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag */
        $flashBag = $this->requestStack->getSession()->getBag('flashes');

        foreach ($notVisibleItems as $cartItem) {
            try {
                $productName = $cartItem->getName();
                $flashBag->add(FlashMessage::KEY_ERROR, $messageTemplate->render(['name' => $productName]));
            } catch (ProductNotFoundException $e) {
                $flashBag->add(
                    FlashMessage::KEY_ERROR,
                    $this->getMessageForNoLongerAvailableProduct(),
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
