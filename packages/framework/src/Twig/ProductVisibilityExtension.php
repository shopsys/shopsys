<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;
use Twig_SimpleFunction;

class ProductVisibilityExtension extends \Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository
     */
    private $productVisibilityRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        ProductVisibilityRepository $productVisibilityRepository,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        Domain $domain
    ) {
        $this->productVisibilityRepository = $productVisibilityRepository;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->domain = $domain;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('isVisibleForDefaultPricingGroup', [$this, 'isVisibleForDefaultPricingGroupOnDomain']),
            new Twig_SimpleFunction(
                'isVisibleForDefaultPricingGroupOnEachDomain',
                [$this, 'isVisibleForDefaultPricingGroupOnEachDomain']
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
        $productVisibility = $this->productVisibilityRepository->getProductVisibility($product, $pricingGroup, $domainId);

        return $productVisibility->isVisible();
    }

    public function isVisibleForDefaultPricingGroupOnEachDomain(Product $product): bool
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if (!$this->isVisibleForDefaultPricingGroupOnDomain($product, $domainConfig->getId())) {
                return false;
            }
        }

        return true;
    }
}
