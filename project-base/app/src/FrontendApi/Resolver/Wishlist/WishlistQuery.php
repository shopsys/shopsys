<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Wishlist;

use App\FrontendApi\Model\Wishlist\WishlistFacade;
use App\Model\Wishlist\Wishlist;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class WishlistQuery extends AbstractQuery
{
    /**
     * @param \App\FrontendApi\Model\Wishlist\WishlistFacade $wishlistFacade
     */
    public function __construct(
        private readonly WishlistFacade $wishlistFacade,
    ) {
    }

    /**
     * @param string|null $wishlistUuid
     * @return \App\Model\Wishlist\Wishlist|null
     */
    public function wishlistQuery(?string $wishlistUuid): ?Wishlist
    {
        return $this->wishlistFacade->getMergedWishlistByUuid($wishlistUuid);
    }
}
