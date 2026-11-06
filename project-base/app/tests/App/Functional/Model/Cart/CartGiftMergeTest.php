<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanDataFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class CartGiftMergeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CartApiFacade $cartApiFacade;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private MergeCartFacade $mergeCartFacade;

    /**
     * @inject
     */
    private CartWatcherFacade $cartWatcherFacade;

    /**
     * @inject
     */
    private GiftPlanFacade $giftPlanFacade;

    /**
     * @inject
     */
    private GiftPlanDataFactory $giftPlanDataFactory;

    private CustomerUser $customerUser;

    private Cart $anonymousCart;

    private Cart $customerCart;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14', Product::class);
        $this->anonymousCart = $this->cartApiFacade->getCartCreateIfNotExists(null, 'dbd7c3b8-f8a2-4d91-a82f-79c3d0d97df0');
        $this->customerCart = $this->cartApiFacade->getCartCreateIfNotExists($this->customerUser, null);
        self::assertSame([], $this->customerCart->getItems());

        $this->cartFacade->addProductToExistingCart($product, 1, $this->anonymousCart);
        $this->cartFacade->addProductToExistingCart($product, 1, $this->customerCart);
    }

    public function testGiftRemovedInAnonymousCartIsRemovedFromCustomerCartAfterMerge(): void
    {
        $giftProduct = $this->removeGiftFromCart($this->anonymousCart);

        $this->mergeAnonymousCartToCustomerCart();

        self::assertTrue($this->customerCart->isProductGiftRemoved($giftProduct));
        self::assertSame([], $this->customerCart->getProductGiftCartItems());
    }

    public function testGiftRemovedInCustomerCartIsNotRestoredByGiftFromMergedAnonymousCart(): void
    {
        $giftProduct = $this->removeGiftFromCart($this->customerCart);

        $this->mergeAnonymousCartToCustomerCart();

        self::assertTrue($this->customerCart->isProductGiftRemoved($giftProduct));
        self::assertSame([], $this->customerCart->getProductGiftCartItems());
    }

    public function testSharedGiftRemovalRemainsSpecificToItsParentAfterLoginMerge(): void
    {
        $otherParent = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $gift = array_first($this->customerCart->getProductGiftCartItems())->getProduct();
        $planData = $this->giftPlanDataFactory->create();
        $planData->name = 'Shared gift after login';
        $planData->domainId = 1;
        $planData->giftProduct = $gift;
        $planData->mainProducts = [$otherParent];
        $this->giftPlanFacade->create($planData);
        $this->cartFacade->addProductToExistingCart($otherParent, 3, $this->customerCart);
        $removedParent = array_first($this->anonymousCart->getProductCartItems())->getProduct();
        $this->removeGiftFromCart($this->anonymousCart);

        $this->mergeAnonymousCartToCustomerCart();
        $this->em->flush();
        $this->em->clear();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($this->customerUser, null);

        self::assertTrue($cart->isProductGiftRemoved($gift, $removedParent));
        self::assertFalse($cart->isProductGiftRemoved($gift, $otherParent));
        $gifts = array_values($cart->getProductGiftCartItems());
        self::assertCount(1, $gifts);
        self::assertSame($otherParent->getId(), $gifts[0]->getGiftMainProduct()->getId());
        self::assertSame(3, $gifts[0]->getQuantity());
    }

    private function removeGiftFromCart(Cart $cart): BaseProduct
    {
        $giftItem = array_first($cart->getProductGiftCartItems());
        self::assertNotNull($giftItem);
        $this->cartFacade->removeItemFromExistingCartByUuid($giftItem->getUuid(), $cart);

        return $giftItem->getProduct();
    }

    private function mergeAnonymousCartToCustomerCart(): void
    {
        $this->mergeCartFacade->mergeCartByUuidToCustomerCart($this->anonymousCart->getCartIdentifier(), $this->customerUser);
        $this->cartWatcherFacade->getCheckedCartWithModifications($this->customerCart);
    }
}
