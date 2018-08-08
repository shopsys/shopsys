<?php

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemRepository;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class CartFacade
{
    const DAYS_LIMIT_FOR_UNREGISTERED = 60;
    const DAYS_LIMIT_FOR_REGISTERED = 120;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartService
     */
    protected $cartService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifierFactory
     */
    protected $customerIdentifierFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    protected $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemRepository
     */
    protected $cartItemRepository;

    public function __construct(
        EntityManagerInterface $em,
        CartService $cartService,
        CartFactory $cartFactory,
        ProductRepository $productRepository,
        CustomerIdentifierFactory $customerIdentifierFactory,
        Domain $domain,
        CurrentCustomer $currentCustomer,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        CartItemRepository $cartItemRepository
    ) {
        $this->em = $em;
        $this->cartService = $cartService;
        $this->cartFactory = $cartFactory;
        $this->productRepository = $productRepository;
        $this->customerIdentifierFactory = $customerIdentifierFactory;
        $this->domain = $domain;
        $this->currentCustomer = $currentCustomer;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->cartItemRepository = $cartItemRepository;
    }
    
    public function addProductToCart(int $productId, int $quantity): \Shopsys\FrameworkBundle\Model\Cart\AddProductResult
    {
        $product = $this->productRepository->getSellableById(
            $productId,
            $this->domain->getId(),
            $this->currentCustomer->getPricingGroup()
        );
        $customerIdentifier = $this->customerIdentifierFactory->get();
        $cart = $this->cartFactory->get($customerIdentifier);
        $result = $this->cartService->addProductToCart($cart, $customerIdentifier, $product, $quantity);
        /* @var $result \Shopsys\FrameworkBundle\Model\Cart\AddProductResult */

        $this->em->persist($result->getCartItem());
        $this->em->flush();

        return $result;
    }

    public function changeQuantities(array $quantitiesByCartItemId): void
    {
        $cart = $this->getCartOfCurrentCustomer();
        $this->cartService->changeQuantities($cart, $quantitiesByCartItemId);
        $this->em->flush();
    }
    
    public function deleteCartItem(int $cartItemId): void
    {
        $cart = $this->getCartOfCurrentCustomer();
        $cartItemToDelete = $this->cartService->getCartItemById($cart, $cartItemId);
        $cart->removeItemById($cartItemId);
        $this->em->remove($cartItemToDelete);
        $this->em->flush();
    }

    public function cleanCart(): void
    {
        $cart = $this->getCartOfCurrentCustomer();
        $cartItemsToDelete = $cart->getItems();
        $this->cartService->cleanCart($cart);

        foreach ($cartItemsToDelete as $cartItemToDelete) {
            $this->em->remove($cartItemToDelete);
        }

        $this->em->flush();

        $this->cleanAdditionalData();
    }
    
    public function getProductByCartItemId(int $cartItemId): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $cart = $this->getCartOfCurrentCustomer();

        return $this->cartService->getCartItemById($cart, $cartItemId)->getProduct();
    }

    public function cleanAdditionalData(): void
    {
        $this->currentPromoCodeFacade->removeEnteredPromoCode();
    }
    public function getCartOfCurrentCustomer(): \Shopsys\FrameworkBundle\Model\Cart\Cart
    {
        $customerIdentifier = $this->customerIdentifierFactory->get();

        return $this->cartFactory->get($customerIdentifier);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function getQuantifiedProductsOfCurrentCustomerIndexedByCartItemId(): array
    {
        $cart = $this->getCartOfCurrentCustomer();

        return $this->cartService->getQuantifiedProductsIndexedByCartItemId($cart);
    }

    public function deleteOldCarts(): void
    {
        $this->cartItemRepository->deleteOldCartsForUnregisteredCustomers(self::DAYS_LIMIT_FOR_UNREGISTERED);
        $this->cartItemRepository->deleteOldCartsForRegisteredCustomers(self::DAYS_LIMIT_FOR_REGISTERED);
        $this->cartFactory->clearCache();
    }
}
