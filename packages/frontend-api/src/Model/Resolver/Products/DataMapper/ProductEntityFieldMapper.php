<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;
use Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade;

class ProductEntityFieldMapper
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductCollectionFacade $productCollectionFacade,
        protected readonly ProductAccessoryFacade $productAccessoryFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getShortDescription(Product $product): ?string
    {
        return $product->getShortDescription($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getLink(Product $product): string
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            [$product->getId()],
            $this->domain->getCurrentDomainConfig(),
        );

        return $absoluteUrlsIndexedByProductId[$product->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories(Product $product): array
    {
        return $product->getCategoriesIndexedByDomainId()[$this->domain->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function getAvailability(Product $product): Availability
    {
        return $product->getCalculatedAvailability();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return bool
     */
    public function isSellingDenied(Product $product): bool
    {
        return $product->getCalculatedSellingDenied();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAccessories(Product $product): array
    {
        return $this->productAccessoryFacade->getAllAccessories(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getDescription(Product $product): ?string
    {
        return $product->getDescription($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues[]
     */
    public function getParameters(Product $product): array
    {
        return $this->parameterWithValuesFactory->createMultipleForProduct($product);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getSeoH1(Product $product): ?string
    {
        return $product->getSeoH1($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getSeoTitle(Product $product): ?string
    {
        return $product->getSeoTitle($this->domain->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string|null
     */
    public function getSeoMetaDescription(Product $product): ?string
    {
        return $product->getSeoMetaDescription($this->domain->getId());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return int
     */
    public function getOrderingPriority(Product $product): int
    {
        return $product->getOrderingPriority($this->domain->getId());
    }
}
