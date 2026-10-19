<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
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
     * @return iterable<string, array{bool}>
     */
    public static function removedGiftCartProvider(): iterable
    {
        yield 'removed in anonymous cart' => [true];

        yield 'removed in customer cart' => [false];
    }

    #[DataProvider('removedGiftCartProvider')]
    public function testRemovedGiftStaysRemovedAfterCartMerge(bool $removeFromAnonymousCart): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14', Product::class);
        $anonymousCart = $this->cartApiFacade->getCartCreateIfNotExists(null, 'dbd7c3b8-f8a2-4d91-a82f-79c3d0d97df0');
        $customerCart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, null);
        $this->cartApiFacade->deleteCart($customerCart);
        $customerCart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, null);
        $this->cartFacade->addProductToExistingCart($product, 1, $anonymousCart);
        $this->cartFacade->addProductToExistingCart($product, 1, $customerCart);
        $cartToRemoveGiftFrom = $removeFromAnonymousCart ? $anonymousCart : $customerCart;
        $giftItem = array_first($cartToRemoveGiftFrom->getProductGiftCartItems());
        self::assertNotNull($giftItem);
        $giftProduct = $giftItem->getProduct();
        $this->cartFacade->removeItemFromExistingCartByUuid($giftItem->getUuid(), $cartToRemoveGiftFrom);

        $this->mergeCartFacade->mergeCartByUuidToCustomerCart($anonymousCart->getCartIdentifier(), $customerUser);
        $this->cartWatcherFacade->getCheckedCartWithModifications($customerCart);
        $this->cartFacade->addProductToExistingCart($product, 3, $customerCart, true);

        self::assertTrue($customerCart->isProductGiftRemoved($giftProduct));
        self::assertSame([], $customerCart->getProductGiftCartItems());
        self::assertSame(3, array_first($customerCart->getProductCartItems())->getQuantity());
    }
}
