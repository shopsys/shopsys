<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Wishlist\Exception;

class WishlistItemAlreadyExistsException extends WishlistException
{
    protected const CODE = 'wishlist-item-already-exists';
}
