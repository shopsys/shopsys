<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Wishlist;

use App\FrontendApi\Model\Wishlist\WishlistFacade;
use App\Model\Customer\User\CurrentCustomerUser;
use App\Model\Product\ProductFacade;
use App\Model\Wishlist\Wishlist;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;

class WishlistMutation extends AbstractMutation
{
    /**
     * @param \App\FrontendApi\Model\Wishlist\WishlistFacade $wishlistFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        private readonly WishlistFacade $wishlistFacade,
        private readonly ProductFacade $productFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Wishlist\Wishlist
     */
    public function addProductToWishlistMutation(Argument $argument): Wishlist
    {
        $productUuid = $argument['productUuid'];
        $product = $this->productFacade->getByUuid($productUuid);
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        return $this->wishlistFacade->addProductToWishlist($product, $customerUser, $argument['wishlistUuid']);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function removeProductFromWishlistMutation(Argument $argument): ?Wishlist
    {
        $productUuid = $argument['productUuid'];
        $product = $this->productFacade->getByUuid($productUuid);
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        return $this->wishlistFacade->removeProductFromWishlist($product, $customerUser, $argument['wishlistUuid']);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function cleanWishlistMutation(Argument $argument): ?Wishlist
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $this->wishlistFacade->cleanWishlist($customerUser, $argument['wishlistUuid']);

        return null;
    }
}
