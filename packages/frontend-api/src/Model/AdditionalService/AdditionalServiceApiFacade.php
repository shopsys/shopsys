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

        $product = $cartItem->getProduct();
        $enabledAdditionalServiceIds = array_map(
            static fn (AdditionalService $additionalService) => $additionalService->getId(),
            $this->getEnabledByProduct($product),
        );

        $enabledCartItemAdditionalServices = array_values(array_filter(
            $cartItem->getAdditionalServices(),
            static fn (AdditionalService $additionalService) => in_array($additionalService->getId(), $enabledAdditionalServiceIds, true),
        ));

        return $this->additionalServiceQueryDtoFactory->createMultiple($enabledCartItemAdditionalServices, $product);
    }

    /**
     * @param string[] $additionalServiceUuids
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    public function getEnabledByProductAndUuids(Product $product, array $additionalServiceUuids): array
    {
        return array_values(array_filter(
            $this->getEnabledByProduct($product),
            static fn (AdditionalService $additionalService) => in_array($additionalService->getUuid(), $additionalServiceUuids, true),
        ));
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    protected function getEnabledByProduct(Product $product): array
    {
        return $this->additionalServiceFacade->getEnabledByProductIdAndDomainId(
            $product->getId(),
            $this->domain->getId(),
        );
    }
}
