<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Cart;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class CartItemResolverMap extends ResolverMap
{
    protected const string CART_ORDER_DATA_NAMESPACE = 'cart_order_data';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'CartItem' => [
                'discount' => function (CartItem $cartItem) {
                    return $this->getDiscount($cartItem);
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     * @return array
     */
    protected function getDiscount(CartItem $cartItem): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData */
        $orderData = $this->inMemoryCache->getOrSaveValue(
            static::CART_ORDER_DATA_NAMESPACE,
            function () use ($cartItem) {
                return $this->orderDataFactory->createFromCart($cartItem->getCart(), $this->domain->getCurrentDomainConfig());
            },
            $cartItem->getCart()->getCartIdentifier(),
        );

        $discountsData = [];

        foreach ($orderData->items as $orderItem) {
            if ($orderItem->type === OrderItemTypeEnum::TYPE_PRODUCT && $orderItem->product === $cartItem->getProduct()) {
                $discounts = $orderItem->relatedOrderItemsData;

                if (count($discounts) === 0) {
                    return [];
                }

                foreach ($discounts as $discount) {
                    $discountsData[] = [
                        'promoCode' => $discount->promoCode->getCode(),
                        'totalDiscount' => new Price($discount->totalPriceWithoutVat, $discount->totalPriceWithVat),
                        'unitDiscount' => new Price($discount->unitPriceWithoutVat, $discount->unitPriceWithVat),
                    ];
                }
            }
        }

        return $discountsData;
    }
}
