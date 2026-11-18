<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\GiftPlanDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanDataFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
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

    private GiftPlan $giftPlan;

    private Product $productWithGift;

    private Product $giftProduct;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $this->giftPlan = $this->getReferenceForDomain(GiftPlanDataFixture::GIFT_PLAN_VALID, Domain::FIRST_DOMAIN_ID, GiftPlan::class);

        /** @var \App\Model\Product\Product $productWithGift */
        $productWithGift = array_first($this->giftPlan->getMainProducts());
        $this->productWithGift = $productWithGift;

        /** @var \App\Model\Product\Product $giftProduct */
        $giftProduct = $this->giftPlan->getGiftProduct();
        $this->giftProduct = $giftProduct;

        $this->anonymousCart = $this->cartApiFacade->getCartCreateIfNotExists(null, 'dbd7c3b8-f8a2-4d91-a82f-79c3d0d97df0');
        $this->customerCart = $this->cartApiFacade->getCartCreateIfNotExists($this->customerUser, null);
        self::assertSame([], $this->customerCart->getItems());

        $this->cartFacade->addProductToExistingCart($this->productWithGift, 1, $this->anonymousCart);
        $this->cartFacade->addProductToExistingCart($this->productWithGift, 1, $this->customerCart);

        $this->assertCartContainsOneGift($this->anonymousCart);
        $this->assertCartContainsOneGift($this->customerCart);
    }

    public function testGiftRemovedInAnonymousCartIsRemovedFromCustomerCartAfterMerge(): void
    {
        $this->removeGiftFromCart($this->anonymousCart);

        $this->mergeAnonymousCartToCustomerCart();

        self::assertTrue($this->customerCart->isProductGiftRemoved($this->productWithGift, $this->giftProduct));
        self::assertSame([], $this->customerCart->getProductGiftCartItems());
    }

    public function testGiftRemovedInCustomerCartIsNotRestoredByGiftFromMergedAnonymousCart(): void
    {
        $this->removeGiftFromCart($this->customerCart);

        $this->mergeAnonymousCartToCustomerCart();

        self::assertTrue($this->customerCart->isProductGiftRemoved($this->productWithGift, $this->giftProduct));
        self::assertSame([], $this->customerCart->getProductGiftCartItems());
    }

    public function testSharedGiftRemovalRemainsSpecificToItsParentAfterLoginMerge(): void
    {
        $otherParent = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $planData = $this->giftPlanDataFactory->createFromGiftPlan($this->giftPlan);
        $planData->mainProducts[] = $otherParent;
        $this->giftPlanFacade->edit($this->giftPlan->getId(), $planData);
        $this->cartFacade->addProductToExistingCart($otherParent, 3, $this->customerCart);
        $this->removeGiftFromCart($this->anonymousCart);

        $this->mergeAnonymousCartToCustomerCart();
        $this->em->flush();
        $this->em->clear();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($this->customerUser, null);

        self::assertTrue($cart->isProductGiftRemoved($this->productWithGift, $this->giftProduct));
        self::assertFalse($cart->isProductGiftRemoved($otherParent, $this->giftProduct));
        $gifts = array_values($cart->getProductGiftCartItems());
        self::assertCount(1, $gifts);
        self::assertSame($otherParent->getId(), $cart->findParentItem($gifts[0])->getProduct()->getId());
        self::assertSame(3, $gifts[0]->getQuantity());
    }

    private function assertCartContainsOneGift(Cart $cart): void
    {
        $giftItems = array_values($cart->getProductGiftCartItems());

        self::assertCount(1, $giftItems);
        self::assertSame($this->giftProduct->getId(), $giftItems[0]->getProduct()->getId());
    }

    private function removeGiftFromCart(Cart $cart): void
    {
        $giftItem = array_first($cart->getProductGiftCartItems());
        self::assertNotNull($giftItem);
        $this->cartFacade->removeItemFromExistingCartByUuid($giftItem->getUuid(), $cart);
    }

    private function mergeAnonymousCartToCustomerCart(): void
    {
        $this->mergeCartFacade->mergeCartByUuidToCustomerCart($this->anonymousCart->getCartIdentifier(), $this->customerUser);
        $this->cartWatcherFacade->getCheckedCartWithModifications($this->customerCart);
    }
}
