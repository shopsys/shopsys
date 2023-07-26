<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Wishlist\Exception;

class WishlistItemNotFoundException extends WishlistException
{
    protected const CODE = 'wishlist-item-not-found';
}
