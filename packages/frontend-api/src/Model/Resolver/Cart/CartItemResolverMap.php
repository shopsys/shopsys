<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Cart;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrontendApiBundle\Model\AdditionalService\AdditionalServiceApiFacade;

class CartItemResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdditionalServiceApiFacade $additionalServiceApiFacade,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'CartItem' => [
                'freeQuantity' => function (CartItem $cartItem) {
                    return $cartItem->getFreeQuantity($this->domain->getId());
                },
                'additionalServices' => function (CartItem $cartItem) {
                    return $this->additionalServiceApiFacade->getAdditionalServiceQueryDtosForCartItem($cartItem);
                },
            ],
        ];
    }
}
