<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Cart\Exception\UnavailableCartUserError;
use App\Model\Cart\CartMigrationFacade;
use App\Model\Customer\User\CustomerUser;
use Psr\Log\LoggerInterface;

class MergeCartFacade
{
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Model\Cart\CartMigrationFacade
     */
    private CartMigrationFacade $cartMigrationFacade;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Cart\CartMigrationFacade $cartMigrationFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CartFacade $cartFacade,
        CartMigrationFacade $cartMigrationFacade,
        LoggerInterface $logger
    ) {
        $this->cartFacade = $cartFacade;
        $this->cartMigrationFacade = $cartMigrationFacade;
        $this->logger = $logger;
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function mergeCartByUuidToCustomerCart(string $cartUuid, CustomerUser $customerUser): void
    {
        try {
            $oldCart = $this->cartFacade->getCart(null, $cartUuid);
            $customerCart = $this->cartFacade->getCartCreateIfNotExists($customerUser, null);

            $this->cartMigrationFacade->mergeCarts($oldCart, $customerCart);
        } catch (UnavailableCartUserError $exception) {
            $this->logger->error(
                'Previous cart was not merged for logged user',
                [
                    'cartUuid' => $cartUuid,
                    'customerUserUuid' => $customerUser->getUuid(),
                ]
            );
        }
    }
}
