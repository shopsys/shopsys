<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Payment\PaymentInputData;
use App\FrontendApi\Model\Transport\TransportInputData;
use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class CartWatcherFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher
     */
    private CartWatcher $cartWatcher;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    private CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @var \App\FrontendApi\Model\Cart\TransportAndPaymentWatcherFacade
     */
    private TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private OrderPreviewFactory $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private CurrencyFacade $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Cart\TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        CartWatcher $cartWatcher,
        EntityManagerInterface $em,
        CurrentCustomerUser $currentCustomerUser,
        ProductAvailabilityFacade $productAvailabilityFacade,
        Domain $domain,
        TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade,
        OrderPreviewFactory $orderPreviewFactory,
        CurrencyFacade $currencyFacade
    ) {
        $this->cartWatcher = $cartWatcher;
        $this->em = $em;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->domain = $domain;
        $this->transportAndPaymentWatcherFacade = $transportAndPaymentWatcherFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\FrontendApi\Model\Transport\TransportInputData|null $transportInputData
     * @param \App\FrontendApi\Model\Payment\PaymentInputData|null $paymentInputData
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function getCheckedCartWithModifications(
        Cart $cart,
        ?TransportInputData $transportInputData = null,
        ?PaymentInputData $paymentInputData = null
    ): CartWithModificationsResult {
        $this->cartWithModificationsResult = new CartWithModificationsResult($cart);

        $this->checkUnavailableStockQuantityItems($cart);
        $this->checkModifiedPrices($cart);
        $this->checkNotListableItems($cart);

        $this->em->flush();

        $this->loadTotalPrice($cart);

        return $this->transportAndPaymentWatcherFacade->checkTransportAndPayment(
            $this->cartWithModificationsResult,
            $cart,
            $transportInputData,
            $paymentInputData
        );
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkModifiedPrices(Cart $cart): void
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        /** @var \App\Model\Cart\Item\CartItem $cartItem */
        foreach ($modifiedItems as $cartItem) {
            $this->cartWithModificationsResult->addCartItemWithModifiedPrice($cartItem);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkNotListableItems(Cart $cart): void
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        /** @var \App\Model\Cart\Item\CartItem $cartItem */
        foreach ($notVisibleItems as $cartItem) {
            $cart->removeItemById($cartItem->getId());
            $this->em->remove($cartItem);

            $this->cartWithModificationsResult->addNoLongerListableCartItem($cartItem);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkUnavailableStockQuantityItems(Cart $cart): void
    {
        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $maximumOrderQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($product, $this->domain->getId());

            if ($maximumOrderQuantity === 0) {
                $cart->removeItemById($cartItem->getId());
                $this->cartWithModificationsResult->addNoLongerAvailableCartItemDueToQuantity($cartItem);

                continue;
            }

            if ($cartItem->getQuantity() <= $maximumOrderQuantity) {
                continue;
            }

            $cartItem->changeQuantity($maximumOrderQuantity);
            $cartItem->changeAddedAt(new DateTime());
            $this->em->persist($cartItem);

            $this->cartWithModificationsResult->addCartItemWithChangedQuantity($cartItem);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function loadTotalPrice(Cart $cart)
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $currentCustomerUser */
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $orderPreview = $this->orderPreviewFactory->create(
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
            $cart->getQuantifiedProducts(),
            null,
            null,
            $currentCustomerUser
        );

        $this->cartWithModificationsResult->setTotalPrice($orderPreview->getTotalPrice());
    }
}
