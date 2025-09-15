<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Cart;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;

class CartItemResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade $giftPlanSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
    protected function map(): array
    {
        $map['CartItem'] = [
            'baseGiftPrice' => function () {
                return $this->giftPlanSettingFacade->getGiftPriceWithVat($this->domain->getId());
            },
        ];

        return $map;
    }
}
