<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductVisibilityExtension extends AbstractExtension
{
    public function __construct(
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isVisibleForDefaultPricingGroup', $this->isVisibleForDefaultPricingGroupOnDomain(...)),
            new TwigFunction(
                'isVisibleForDefaultPricingGroupOnEachDomain',
                $this->isVisibleForDefaultPricingGroupOnEachDomain(...),
            ),
            new TwigFunction(
                'isVisibleForDefaultPricingGroupOnSomeDomain',
                $this->isVisibleForDefaultPricingGroupOnSomeDomain(...),
            ),
            new TwigFunction(
                'isSellableOnDomain',
                $this->isSellableOnDomain(...),
            ),
        ];
    }

    public function getName(): string
    {
        return 'product_visibility';
    }

    public function isVisibleForDefaultPricingGroupOnDomain(Product $product, int $domainId): bool
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        $productVisibility = $this->productVisibilityFacade->getProductVisibility(
            $product,
            $pricingGroup,
            $domainId,
        );

        return $productVisibility->isVisible();
    }

    public function isVisibleForDefaultPricingGroupOnEachDomain(Product $product): bool
    {
        $defaultPricingGroupIdsIndexedByDomainId = $this->pricingGroupSettingFacade->getAllDefaultPricingGroupsIdsIndexedByDomainId();

        return $this->productVisibilityFacade->isProductVisibleOnAllDomains(
            $product,
            $defaultPricingGroupIdsIndexedByDomainId,
        );
    }

    public function isVisibleForDefaultPricingGroupOnSomeDomain(Product $product): bool
    {
        $defaultPricingGroupIdsIndexedByDomainId = $this->pricingGroupSettingFacade->getAllDefaultPricingGroupsIdsIndexedByDomainId();

        return $this->productVisibilityFacade->isProductVisibleOnSomeDomains(
            $product,
            $defaultPricingGroupIdsIndexedByDomainId,
        );
    }

    public function isSellableOnDomain(Product $product, int $domainId): bool
    {
        $calculatedSellingDeniedPerDomainIds = $this->productFacade->getCalculatedSellingDeniedPerDomainIds($product);

        return !$calculatedSellingDeniedPerDomainIds[$domainId];
    }
}
