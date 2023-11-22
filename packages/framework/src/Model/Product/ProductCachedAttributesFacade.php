<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Symfony\Contracts\Service\ResetInterface;

class ProductCachedAttributesFacade implements ResetInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice[]
     */
    protected array $sellingPricesByProductId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[][]
     */
    protected array $parameterValuesByProductId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getProductSellingPrice(Product $product): ?\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        if (isset($this->sellingPricesByProductId[$product->getId()])) {
            return $this->sellingPricesByProductId[$product->getId()];
        }

        try {
            $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser($product);
        } catch (MainVariantPriceCalculationException $ex) {
            $productPrice = null;
        }
        $this->sellingPricesByProductId[$product->getId()] = $productPrice;

        return $productPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product): array
    {
        if (isset($this->parameterValuesByProductId[$product->getId()])) {
            return $this->parameterValuesByProductId[$product->getId()];
        }
        $locale = $this->localization->getLocale();

        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByName(
            $product,
            $locale,
        );

        foreach ($productParameterValues as $index => $productParameterValue) {
            $parameter = $productParameterValue->getParameter();

            if ($parameter->getName() === null
                || $productParameterValue->getValue()->getLocale() !== $locale
            ) {
                unset($productParameterValues[$index]);
            }
        }
        $this->parameterValuesByProductId[$product->getId()] = $productParameterValues;

        return $productParameterValues;
    }

    public function reset(): void
    {
        $this->sellingPricesByProductId = [];
        $this->parameterValuesByProductId = [];
    }
}
