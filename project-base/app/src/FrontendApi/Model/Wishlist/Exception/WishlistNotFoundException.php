<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Wishlist\Exception;

class WishlistNotFoundException extends WishlistException
{
    protected const CODE = 'wishlist-not-found';
}
