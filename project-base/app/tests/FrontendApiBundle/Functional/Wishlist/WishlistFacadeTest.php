<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Wishlist;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\FrontendApi\Model\Wishlist\WishlistFacade;
use App\Model\Wishlist\WishlistRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class WishlistFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private WishlistFacade $wishlistFacade;

    /**
     * @inject
     */
    private WishlistRepository $wishlistRepository;

    public function testAddProductToNotExistingWishlist(): void
    {

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $wishlist = $this->wishlistFacade->addProductToWishlist($product, $customerUser, null);

        $this->assertSame(1, $wishlist->getItemsCount());
    }

    public function testRemoveLastProductFromWishlist(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $wishlist = $this->wishlistFacade->addProductToWishlist($product, $customerUser, null);

        $this->assertSame(1, $wishlist->getItemsCount());


        $returnedWishlist = $this->wishlistFacade->removeProductFromWishlist($product, $customerUser, null);
        $this->assertNull($returnedWishlist);
    }

    public function testCleanWishlist(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $wishlist = $this->wishlistFacade->addProductToWishlist($product, $customerUser, null);

        $this->assertSame(1, $wishlist->getItemsCount());
        $wishlistId = $wishlist->getId();

        $this->wishlistFacade->cleanWishlist($customerUser, null);

        $actualWishlist = $this->wishlistRepository->findById($wishlistId);

        $this->assertNull($actualWishlist);
    }
}
