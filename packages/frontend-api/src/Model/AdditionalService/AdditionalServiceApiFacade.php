<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Product\Product;

class AdditionalServiceApiFacade
{
    public function __construct(
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
        protected readonly AdditionalServiceQueryDtoFactory $additionalServiceQueryDtoFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\AdditionalService\AdditionalServiceQueryDto[]
     */
    public function getAdditionalServiceQueryDtosForCartItem(CartItem $cartItem): array
    {
        if (!$cartItem->hasProduct()) {
            return [];
        }

        return $this->additionalServiceQueryDtoFactory->createMultiple(
            $cartItem->getAdditionalServices(),
            $cartItem->getProduct(),
        );
    }

    /**
     * @param string[] $additionalServiceUuids
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getEnabledByProductAndUuids(Product $product, array $additionalServiceUuids): array
    {
        $enabledAdditionalServices = $this->additionalServiceFacade->getEnabledByProductIdAndDomainId(
            $product->getId(),
            $this->domain->getId(),
        );

        return array_values(array_filter(
            $enabledAdditionalServices,
            static fn (AdditionalService $additionalService) => in_array($additionalService->getUuid(), $additionalServiceUuids, true),
        ));
    }
}
