<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Cart;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

class CartItemResolverMap extends ResolverMap
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'CartItem' => [
                'freeQuantity' => function (CartItem $cartItem) {
                    return $cartItem->getFreeQuantity($this->domain->getId());
                },
            ],
        ];
    }
}
