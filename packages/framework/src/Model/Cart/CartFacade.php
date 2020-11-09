<?php

namespace Shopsys\FrameworkBundle\Model\Cart;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class CartFacade
{
    protected const DAYS_LIMIT_FOR_UNREGISTERED = 60;
    protected const DAYS_LIMIT_FOR_REGISTERED = 120;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory
     */
    protected $customerUserIdentifierFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    protected $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser
     */
    protected $productPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     */
    protected $cartItemFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartRepository
     */
    protected $cartRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade
     */
    protected $cartWatcherFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade $cartWatcherFacade
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
        CartWatcherFacade $cartWatcherFacade
    ) {
        $this->em = $em;
        $this->cartFactory = $cartFactory;
        $this->productRepository = $productRepository;
        $this->customerUserIdentifierFactory = $customerUserIdentifierFactory;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->productPriceCalculation = $productPriceCalculation;
        $this->cartItemFactory = $cartItemFactory;
        $this->cartRepository = $cartRepository;
        $this->cartWatcherFacade = $cartWatcherFacade;
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @return \Shopsys\FrameworkBundle\Model\Cart\AddProductResult
     */
    public function addProductToCart($productId, $quantity)
    {
        $product = $this->productRepository->getSellableById(
            $productId,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );
        $cart = $this->getCartOfCurrentCustomerUserCreateIfNotExists();

        if (!is_int($quantity) || $quantity <= 0) {
            throw new InvalidQuantityException($quantity);
        }

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct() === $product) {
                $item->changeQuantity($item->getQuantity() + $quantity);
                $item->changeAddedAt(new DateTime());
                $result = new AddProductResult($item, false, $quantity);
                $this->em->persist($result->getCartItem());
                $this->em->flush();

                return $result;
            }
        }
        $productPrice = $this->productPriceCalculation->calculatePriceForCurrentUser($product);
        $newCartItem = $this->cartItemFactory->create($cart, $product, $quantity, $productPrice->getPriceWithVat());
        $cart->addItem($newCartItem);
        $cart->setModifiedNow();

        $result = new AddProductResult($newCartItem, true, $quantity);

        $this->em->persist($result->getCartItem());
        $this->em->flush();

        return $result;
    }

    /**
     * @param array $quantitiesByCartItemId
     */
    public function changeQuantities(array $quantitiesByCartItemId)
    {
        $cart = $this->findCartOfCurrentCustomerUser();

        if ($cart === null) {
            return;
        }

        $cart->changeQuantities($quantitiesByCartItemId);
        $this->em->flush();
    }

    /**
     * @param int $cartItemId
     */
    public function deleteCartItem($cartItemId)
    {
        $cart = $this->findCartOfCurrentCustomerUser();

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

    public function deleteCartOfCurrentCustomerUser()
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();

        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        if ($cart !== null) {
            $this->deleteCart($cart);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function deleteCart(Cart $cart)
    {
        $this->em->remove($cart);
        $this->em->flush();

        $this->cleanAdditionalData();
    }

    /**
     * @param int $cartItemId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProductByCartItemId($cartItemId)
    {
        $cart = $this->findCartOfCurrentCustomerUser();

        if ($cart === null) {
            $message = 'CartItem with id = ' . $cartItemId . ' not found in cart.';
            throw new InvalidCartItemException($message);
        }

        return $cart->getItemById($cartItemId)->getProduct();
    }

    public function cleanAdditionalData()
    {
        $this->currentPromoCodeFacade->removeEnteredPromoCode();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findCartByCustomerUserIdentifier(CustomerUserIdentifier $customerUserIdentifier)
    {
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        if ($cart !== null) {
            $this->cartWatcherFacade->checkCartModifications($cart);

            if ($cart->isEmpty()) {
                $this->deleteCart($cart);

                return null;
            }
        }

        return $cart;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findCartOfCurrentCustomerUser()
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();

        return $this->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function getCartOfCurrentCustomerUserCreateIfNotExists()
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();

        return $this->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function getCartByCustomerUserIdentifierCreateIfNotExists(CustomerUserIdentifier $customerUserIdentifier)
    {
        $cart = $this->cartRepository->findByCustomerUserIdentifier($customerUserIdentifier);

        if ($cart === null) {
            $cart = $this->cartFactory->create($customerUserIdentifier);

            $this->em->persist($cart);
            $this->em->flush($cart);
        }

        return $cart;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProductsOfCurrentCustomer()
    {
        $cart = $this->findCartOfCurrentCustomerUser();

        if ($cart === null) {
            return [];
        }

        return $cart->getQuantifiedProducts();
    }

    public function deleteOldCarts()
    {
        $this->cartRepository->deleteOldCartsForUnregisteredCustomerUsers(static::DAYS_LIMIT_FOR_UNREGISTERED);
        $this->cartRepository->deleteOldCartsForRegisteredCustomerUsers(static::DAYS_LIMIT_FOR_REGISTERED);
    }
}
