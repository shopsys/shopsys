<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Wishlist;

use App\FrontendApi\Model\Wishlist\Exception\HandlingWithOtherLoggedCustomerWishlistException;
use App\FrontendApi\Model\Wishlist\Exception\WishlistItemAlreadyExistsException;
use App\FrontendApi\Model\Wishlist\Exception\WishlistItemNotFoundException;
use App\FrontendApi\Model\Wishlist\Exception\WishlistNotFoundException;
use App\Model\Customer\User\CurrentCustomerUser;
use App\Model\Customer\User\CustomerUser;
use App\Model\Product\Product;
use App\Model\Wishlist\Item\WishlistItem;
use App\Model\Wishlist\Wishlist;
use App\Model\Wishlist\WishlistFacade as AppWishlistFacade;
use App\Model\Wishlist\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;

class WishlistFacade
{
    /**
     * @param \App\Model\Wishlist\WishlistFacade $wishlistFacade
     * @param \App\Model\Wishlist\WishlistRepository $wishlistRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        private readonly AppWishlistFacade $wishlistFacade,
        private readonly WishlistRepository $wishlistRepository,
        private readonly EntityManagerInterface $em,
        private readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param string $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist
     */
    public function getWishlistByUuid(string $wishlistUuid): Wishlist
    {
        $wishlist = $this->wishlistFacade->findWishlistByUuid($wishlistUuid);

        if ($wishlist === null) {
            throw new WishlistNotFoundException(sprintf('Wishlist %s not found.', $wishlistUuid));
        }

        return $wishlist;
    }

    /**
     * @param \App\Model\Wishlist\Wishlist $wishlist
     */
    public function setUpdatedAtToNow(Wishlist $wishlist): void
    {
        $wishlist->setUpdatedAtToNow();
        $this->em->flush();
    }

    /**
     * @param string|null $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist
     */
    public function getOrCreateWishlistByUuid(?string $wishlistUuid): Wishlist
    {
        if ($wishlistUuid === null) {
            return $this->wishlistFacade->createWishlist();
        }

        $wishlist = $this->wishlistRepository->findByUuid($wishlistUuid);

        if ($wishlist === null) {
            $wishlist = $this->wishlistFacade->createWishlist();
        }

        return $wishlist;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Wishlist\Wishlist
     */
    public function getOrCreateWishlistOfCustomerUser(CustomerUser $customerUser): Wishlist
    {
        $wishlist = $this->wishlistRepository->findByCustomerUser($customerUser);

        if ($wishlist === null) {
            $wishlist = $this->wishlistFacade->createWishlistByCustomerUser($customerUser);
        }

        return $wishlist;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Wishlist\Wishlist
     */
    public function getWishlistOfCustomerUser(CustomerUser $customerUser): Wishlist
    {
        $wishlist = $this->wishlistFacade->findWishlistOfCustomerUser($customerUser);

        if ($wishlist === null) {
            throw new WishlistNotFoundException('Current customer has no wishlist.');
        }

        return $wishlist;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist
     */
    public function addProductToWishlist(
        Product $product,
        ?CustomerUser $customerUser,
        ?string $wishlistUuid,
    ): Wishlist {
        if ($customerUser === null) {
            $wishlist = $this->getOrCreateWishlistByUuid($wishlistUuid);

            if ($wishlist->getCustomerUser() !== null) {
                throw new HandlingWithOtherLoggedCustomerWishlistException('Handling with different customer wishlist.');
            }
        } else {
            $wishlist = $this->getOrCreateWishlistOfCustomerUser($customerUser);

            if ($wishlistUuid !== null) {
                $wishlistByUuid = $this->wishlistFacade->findWishlistByUuid($wishlistUuid);

                if ($wishlistByUuid !== null) {
                    $wishlist = $this->wishlistFacade->mergeWishlists($wishlist, $wishlistByUuid);
                }
            }
        }

        if ($wishlist->isProductInWishlist($product)) {
            throw new WishlistItemAlreadyExistsException(sprintf('Product %s in wishlist already exists.', $product->getName()));
        }

        return $this->wishlistFacade->addProductToWishlist($product, $wishlist);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @return \App\Model\Wishlist\Item\WishlistItem
     */
    private function getWishlistItemByProduct(Product $product, Wishlist $wishlist): WishlistItem
    {
        foreach ($wishlist->getItems() as $wishlistItem) {
            if ($wishlistItem->getProduct()->getId() === $product->getId()) {
                return $wishlistItem;
            }
        }

        throw new WishlistItemNotFoundException(sprintf('Product %s in wishlist not found.', $product->getName()));
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function removeProductFromWishlist(
        Product $product,
        ?CustomerUser $customerUser,
        ?string $wishlistUuid,
    ): ?Wishlist {
        $wishlist = $this->getWishlistByCustomerUserOrUuid($customerUser, $wishlistUuid);

        $this->setUpdatedAtToNow($wishlist);

        $wishlistItem = $this->getWishlistItemByProduct($product, $wishlist);

        return $this->wishlistFacade->removeWishlistItemFromWishlist($wishlistItem, $wishlist);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $wishlistUuid
     */
    public function cleanWishlist(?CustomerUser $customerUser, ?string $wishlistUuid): void
    {
        $wishlist = $this->getWishlistByCustomerUserOrUuid($customerUser, $wishlistUuid);

        $this->wishlistFacade->cleanWishlist($wishlist);
    }

    /**
     * @param string|null $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function getMergedWishlistByUuid(?string $wishlistUuid): ?Wishlist
    {
        $loggedCustomerWishlist = null;
        $wishlistByUuid = null;
        $mergedWishlist = null;
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser !== null) {
            try {
                $loggedCustomerWishlist = $this->getWishlistOfCustomerUser($currentCustomerUser);
                $this->setUpdatedAtToNow($loggedCustomerWishlist);
            } catch (WishlistNotFoundException $exception) {
                $loggedCustomerWishlist = null;
            }
        }

        if ($wishlistUuid === null) {
            return $loggedCustomerWishlist;
        }

        if ($loggedCustomerWishlist === null || $loggedCustomerWishlist->getUuid() !== $wishlistUuid) {
            try {
                $wishlistByUuid = $this->getWishlistByUuid($wishlistUuid);
                $this->setUpdatedAtToNow($wishlistByUuid);
            } catch (WishlistNotFoundException $exception) {
                return $loggedCustomerWishlist;
            }
        }

        if ($loggedCustomerWishlist !== null && $wishlistByUuid !== null) {
            $mergedWishlist = $this->wishlistFacade->mergeWishlists($loggedCustomerWishlist, $wishlistByUuid);
        } elseif ($currentCustomerUser !== null && $wishlistByUuid !== null) {
            if ($wishlistByUuid->getCustomerUser() !== null) {
                throw new HandlingWithOtherLoggedCustomerWishlistException();
            }
            $mergedWishlist = $this->wishlistFacade->setCustomerUserToWishlist($currentCustomerUser, $wishlistByUuid);
        }

        return $mergedWishlist ?? $loggedCustomerWishlist ?? $wishlistByUuid;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Wishlist\Wishlist $wishlist
     * @return \App\Model\Wishlist\Wishlist
     */
    public function setCustomerUserToWishlist(CustomerUser $customerUser, Wishlist $wishlist): Wishlist
    {
        if ($wishlist->getCustomerUser() !== null) {
            throw new HandlingWithOtherLoggedCustomerWishlistException('Handling with different customer wishlist.');
        }

        return $this->wishlistFacade->setCustomerUserToWishlist($customerUser, $wishlist);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist
     */
    private function getWishlistByCustomerUserOrUuid(?CustomerUser $customerUser, ?string $wishlistUuid): Wishlist
    {
        if ($customerUser !== null) {
            return $this->getWishlistOfCustomerUser($customerUser);
        }

        if ($wishlistUuid === null) {
            throw new WishlistNotFoundException('Wishlist not found.');
        }

        return $this->getWishlistByUuid($wishlistUuid);
    }
}
