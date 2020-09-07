<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Category\CategoryFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade as BaseCartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\CartRepository;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation
 * @property \App\Model\Cart\Watcher\CartWatcherFacade $cartWatcherFacade
 * @method deleteCart(\App\Model\Cart\Cart $cart)
 * @method \App\Model\Product\Product getProductByCartItemId(int $cartItemId)
 * @method \App\Model\Cart\Cart|null findCartOfCurrentCustomerUser()
 * @method \App\Model\Cart\Cart getCartOfCurrentCustomerUserCreateIfNotExists()
 * @method \App\Model\Cart\Cart getCartByCustomerUserIdentifierCreateIfNotExists(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @property \App\Component\Domain\Domain $domain
 */
class CartFacade extends BaseCartFacade
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    private $flashBag;

    /**
     * @var \Twig\Environment
     */
    protected $twigEnvironment;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository
     * @param \App\Model\Cart\Watcher\CartWatcherFacade $cartWatcherFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Twig\Environment $twigEnvironment
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CartFactory $cartFactory,
        ProductRepository $productRepository,
        CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        ProductPriceCalculationForCustomerUser $productPriceCalculation,
        CartItemFactoryInterface $cartItemFactory,
        CartRepository $cartRepository,
        CartWatcherFacade $cartWatcherFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        FlashBagInterface $flashBag,
        Environment $twigEnvironment,
        CategoryFacade $categoryFacade
    ) {
        parent::__construct(
            $em,
            $cartFactory,
            $productRepository,
            $customerUserIdentifierFactory,
            $domain,
            $currentCustomerUser,
            $currentPromoCodeFacade,
            $productPriceCalculation,
            $cartItemFactory,
            $cartRepository,
            $cartWatcherFacade
        );
        $this->productAvailabilityFacade = $productAvailabilityFacade;
        $this->flashBag = $flashBag;
        $this->twigEnvironment = $twigEnvironment;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @return \App\Model\Cart\AddProductResult
     */
    public function addProductToCart($productId, $quantity): AddProductResult
    {
        $product = $this->productRepository->getSellableById(
            $productId,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );

        $cart = $this->getCartOfCurrentCustomerUserCreateIfNotExists();

        $maximumOrderQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($product, $this->domain->getId());
        $notOnStockQuantity = 0;

        $overLimitQuantity = $this->categoryFacade->getOverLimitQuantity($product, $this->domain->getId());

        if (!is_int($quantity) || $quantity <= 0) {
            throw new \Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException($quantity);
        }

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() === $product) {
                $newQuantity = $item->getQuantity() + $quantity;
                if ($newQuantity > $maximumOrderQuantity) {
                    $notOnStockQuantity = $newQuantity - $maximumOrderQuantity;
                    $newQuantity = $maximumOrderQuantity;
                }
                $isQuantityOverLimit = $this->isQuantityOverLimitReached($newQuantity, $overLimitQuantity);
                $item->changeQuantity($newQuantity);
                $item->changeAddedAt(new DateTime());
                $result = new AddProductResult($item, false, $quantity, $notOnStockQuantity, $overLimitQuantity, $isQuantityOverLimit);
                $this->em->persist($result->getCartItem());
                $this->em->flush();

                return $result;
            }
        }

        if ($quantity > $maximumOrderQuantity) {
            $notOnStockQuantity = $quantity - $maximumOrderQuantity;
            $quantity = $maximumOrderQuantity;
        }

        $isQuantityOverLimit = $this->isQuantityOverLimitReached($quantity, $overLimitQuantity);
        $productPrice = $this->productPriceCalculation->calculatePriceForCurrentUser($product);
        /** @var \App\Model\Cart\Item\CartItem $newCartItem */
        $newCartItem = $this->cartItemFactory->create($cart, $product, $quantity, $productPrice->getPriceWithVat());
        $cart->addItem($newCartItem);
        $cart->setModifiedNow();

        $result = new AddProductResult($newCartItem, true, $quantity, $notOnStockQuantity, $overLimitQuantity, $isQuantityOverLimit);

        $this->em->persist($result->getCartItem());
        $this->em->flush();

        return $result;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \App\Model\Cart\Cart|null
     */
    public function findCartByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier)
    {
        /** @var \App\Model\Cart\Cart $cart */
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        if ($cart !== null) {
            $this->cartWatcherFacade->checkCartModifications($cart);
            $cartItemsToDelete = $this->cartWatcherFacade->checkUnavailableStockQuantityItems($cart);

            foreach ($cartItemsToDelete as $carItemToDelete) {
                $this->deleteCartItem($carItemToDelete->getId(), $cart);

                $messageTemplate = $this->twigEnvironment->createTemplate(
                    t('Z Vašeho košíku bylo z důvodu nedostupnosti odstraněno zbožéí: <strong>{{ name }}</strong>. Prosím zkontrolujte si svojí objednávku.')
                );
                $this->flashBag->add(FlashMessage::KEY_INFO, $messageTemplate->render(['name' => $carItemToDelete->getName()]));
            }

            if ($cart->isEmpty()) {
                $this->deleteCart($cart);

                return null;
            }
        }

        return $cart;
    }

    /**
     * @param int $cartItemId
     * @param \App\Model\Cart\Cart|null $cart
     */
    public function deleteCartItem($cartItemId, ?Cart $cart = null)
    {
        if (!$cart) {
            $cart = $this->findCartOfCurrentCustomerUser();
        }

        if ($cart === null) {
            return;
        }

        $cartItemToDelete = $cart->getItemById($cartItemId);
        $cart->removeItemById($cartItemId);
        $this->em->remove($cartItemToDelete);
        $this->em->flush();

        if ($cart->isEmpty()) {
            $this->deleteCart($cart);
        }
    }

    /**
     * @param int $quantity
     * @param int|null $quantityLimit
     * @return bool
     */
    private function isQuantityOverLimitReached(int $quantity, ?int $quantityLimit): bool
    {
        if ($quantityLimit === null) {
            return false;
        }

        if ($quantity >= $quantityLimit) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isCartContainsProductWithOverLimitQuantity(): bool
    {
        $cart = $this->findCartOfCurrentCustomerUser();

        if ($cart === null) {
            return false;
        }

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $domainId = $this->domain->getId();
            $itemQuantity = $item->getQuantity();
            $overLimitQuantity = $this->categoryFacade->getOverLimitQuantity($product, $domainId);

            if ($this->isQuantityOverLimitReached($itemQuantity, $overLimitQuantity)) {
                return true;
            }
        }

        return false;
    }
}
