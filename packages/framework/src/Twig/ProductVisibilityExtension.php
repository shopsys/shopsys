<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductVisibilityExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isVisibleForDefaultPricingGroup', [$this, 'isVisibleForDefaultPricingGroupOnDomain']),
            new TwigFunction(
                'isVisibleForDefaultPricingGroupOnEachDomain',
                [$this, 'isVisibleForDefaultPricingGroupOnEachDomain'],
            ),
            new TwigFunction(
                'isVisibleForDefaultPricingGroupOnSomeDomain',
                [$this, 'isVisibleForDefaultPricingGroupOnSomeDomain'],
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    public function isVisibleForDefaultPricingGroupOnEachDomain(Product $product)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if (!$this->isVisibleForDefaultPricingGroupOnDomain($product, $domainConfig->getId())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    public function isVisibleForDefaultPricingGroupOnSomeDomain(Product $product): bool
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($this->isVisibleForDefaultPricingGroupOnDomain($product, $domainConfig->getId())) {
                return true;
            }
        }

        return false;
    }
}
