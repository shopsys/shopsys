<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
        protected readonly Domain $domain,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions()
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'product_visibility';
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isVisibleForDefaultPricingGroupOnDomain(Product $product, $domainId)
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
