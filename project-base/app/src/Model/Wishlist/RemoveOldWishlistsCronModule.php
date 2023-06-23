<?php

declare(strict_types=1);

namespace App\Model\Wishlist;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class RemoveOldWishlistsCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Wishlist\WishlistRepository $wishlistRepository
     */
    public function __construct(
        private readonly WishlistRepository $wishlistRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->wishlistRepository->removeOldWishlists();
    }
}
