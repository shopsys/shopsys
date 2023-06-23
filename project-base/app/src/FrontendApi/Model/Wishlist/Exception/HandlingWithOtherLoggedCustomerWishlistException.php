<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Wishlist\Exception;

class HandlingWithOtherLoggedCustomerWishlistException extends WishlistException
{
    protected const CODE = 'handling-with-logged-customer-wishlist';
}
