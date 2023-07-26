<?php

declare(strict_types=1);

namespace App\Model\Wishlist;

use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use App\Model\Wishlist\Item\WishlistItem;
use Doctrine\ORM\EntityManagerInterface;

class WishlistFacade
{
    /**
     * @param \App\Model\Wishlist\WishlistRepository $wishlistRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private readonly WishlistRepository $wishlistRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @return \App\Model\Wishlist\Wishlist
     */
    public function addProductToWishlist(Product $product, Wishlist $wishlist): Wishlist
    {
        $newWishlistItem = new WishlistItem($wishlist, $product);
        $this->em->persist($newWishlistItem);

        $wishlist->addItem($newWishlistItem);
        $this->em->flush();

        return $wishlist;
    }

    /**
     * @return \App\Model\Wishlist\Wishlist
     */
    public function createWishlist(): Wishlist
    {
        $wishlist = new Wishlist(null);
        $this->em->persist($wishlist);
        $this->em->flush();

        return $wishlist;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Wishlist\Wishlist
     */
    public function createWishlistByCustomerUser(CustomerUser $customerUser): Wishlist
    {
        $wishlist = new Wishlist($customerUser);
        $this->em->persist($wishlist);
        $this->em->flush();

        return $wishlist;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function findWishlistOfCustomerUser(CustomerUser $customerUser): ?Wishlist
    {
        return $this->wishlistRepository->findByCustomerUser($customerUser);
    }

    /**
     * @param string $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function findWishlistByUuid(string $wishlistUuid): ?Wishlist
    {
        return $this->wishlistRepository->findByUuid($wishlistUuid);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @return \App\Model\Wishlist\Wishlist
     */
    public function setCustomerUserToWishlist(CustomerUser $customerUser, Wishlist $wishlist): Wishlist
    {
        $wishlist->setCustomerUser($customerUser);
        $this->em->flush();

        return $wishlist;
    }

    /**
     * @param \App\Model\Wishlist\Wishlist $loggedCustomerWishlist
     * @param \App\Model\Wishlist\Wishlist $wishlistByUuid
     * @return \App\Model\Wishlist\Wishlist
     */
    public function mergeWishlists(Wishlist $loggedCustomerWishlist, Wishlist $wishlistByUuid): Wishlist
    {
        foreach ($wishlistByUuid->getItems() as $wishlistItem) {
            $productFromWishlistByUuid = $wishlistItem->getProduct();

            if ($loggedCustomerWishlist->isProductInWishlist($productFromWishlistByUuid)) {
                continue;
            }

            $newWishlistItem = new WishlistItem($loggedCustomerWishlist, $productFromWishlistByUuid);
            $this->em->persist($newWishlistItem);
            $loggedCustomerWishlist->addItem($newWishlistItem);
        }

        $this->em->remove($wishlistByUuid);
        $this->em->flush();

        return $loggedCustomerWishlist;
    }

    /**
     * @param \App\Model\Wishlist\Item\WishlistItem $wishlistItem
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function removeWishlistItemFromWishlist(WishlistItem $wishlistItem, Wishlist $wishlist): ?Wishlist
    {
        $wishlist->removeItem($wishlistItem);
        $this->em->remove($wishlistItem);
        $this->em->flush();

        if ($wishlist->getItemsCount() === 0) {
            $this->em->remove($wishlist);
            $this->em->flush();

            return null;
        }

        return $wishlist;
    }

    /**
     * @param \App\Model\Wishlist\Wishlist $wishlist
     */
    public function cleanWishlist(Wishlist $wishlist): void
    {
        $this->em->remove($wishlist);
        $this->em->flush();
    }
}
