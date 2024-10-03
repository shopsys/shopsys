<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Cart;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;

class CartItemResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
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
                'discounts' => function (CartItem $cartItem) {
                    return $this->getDiscounts($cartItem);
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     * @return array
     */
    protected function getDiscounts(CartItem $cartItem): array
    {
        $orderData = $this->orderDataFactory->createFromCart($cartItem->getCart(), $this->domain->getCurrentDomainConfig());

        $discountsData = [];

        foreach ($orderData->items as $orderItem) {
            if ($orderItem->type === OrderItemTypeEnum::TYPE_PRODUCT && $orderItem->product === $cartItem->getProduct()) {
                $relatedOrderItemsData = $orderItem->relatedOrderItemsData;

                if (count($relatedOrderItemsData) === 0) {
                    return [];
                }

                foreach ($relatedOrderItemsData as $discount) {
                    if ($discount->type !== OrderItemTypeEnum::TYPE_DISCOUNT) {
                        continue;
                    }

                    $discountsData[] = [
                        'promoCode' => $discount->promoCode->getCode(),
                        'totalDiscount' => $discount->getTotalPrice(),
                        'unitDiscount' => $discount->getUnitPrice(),
                    ];
                }
            }
        }

        return $discountsData;
    }
}
