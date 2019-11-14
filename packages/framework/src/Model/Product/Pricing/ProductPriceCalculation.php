<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    protected $basePriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    protected $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository
     */
    protected $productManualInputPriceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade|null
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository $productManualInputPriceRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade|null $currencyFacade
     */
    public function __construct(
        BasePriceCalculation $basePriceCalculation,
        PricingSetting $pricingSetting,
        ProductManualInputPriceRepository $productManualInputPriceRepository,
        ProductRepository $productRepository,
        ?CurrencyFacade $currencyFacade = null
    ) {
        $this->pricingSetting = $pricingSetting;
        $this->basePriceCalculation = $basePriceCalculation;
        $this->productManualInputPriceRepository = $productManualInputPriceRepository;
        $this->productRepository = $productRepository;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCurrencyFacade(CurrencyFacade $currencyFacade): void
    {
        if ($this->currencyFacade !== null && $this->currencyFacade !== $currencyFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->currencyFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->currencyFacade = $currencyFacade;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function calculatePrice(Product $product, $domainId, PricingGroup $pricingGroup)
    {
        if ($product->isMainVariant()) {
            return $this->calculateMainVariantPrice($product, $domainId, $pricingGroup);
        }

        return $this->calculateProductPriceForPricingGroup($product, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected function calculateMainVariantPrice(Product $mainVariant, $domainId, PricingGroup $pricingGroup)
    {
        $variants = $this->productRepository->getAllSellableVariantsByMainVariant(
            $mainVariant,
            $domainId,
            $pricingGroup
        );
        if (count($variants) === 0) {
            $message = 'Main variant ID = ' . $mainVariant->getId() . ' has no sellable variants.';
            throw new \Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException($message);
        }

        $variantPrices = [];
        foreach ($variants as $variant) {
            $variantPrices[] = $this->calculatePrice($variant, $domainId, $pricingGroup);
        }

        $minVariantPrice = $this->getMinimumPriceByPriceWithoutVat($variantPrices);
        $from = $this->arePricesDifferent($variantPrices);

        return new ProductPrice($minVariantPrice, $from);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected function calculateProductPriceForPricingGroup(Product $product, PricingGroup $pricingGroup)
    {
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup($product, $pricingGroup);
        if ($manualInputPrice !== null) {
            $inputPrice = $manualInputPrice->getInputPrice() ?? Money::zero();
        } else {
            $inputPrice = Money::zero();
        }

        $domainId = $pricingGroup->getDomainId();
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $basePrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $inputPrice,
            $this->pricingSetting->getInputPriceType(),
            $product->getVatForDomain($domainId),
            $defaultCurrency
        );

        return new ProductPrice($basePrice, false);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $prices
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getMinimumPriceByPriceWithoutVat(array $prices)
    {
        if (count($prices) === 0) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidArgumentException('Array can not be empty.');
        }

        $minimumPrice = null;
        foreach ($prices as $price) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null $minimumPrice */
            if ($minimumPrice === null || $price->getPriceWithoutVat()->isLessThan($minimumPrice->getPriceWithoutVat())) {
                $minimumPrice = $price;
            }
        }

        return $minimumPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $prices
     * @return bool
     */
    public function arePricesDifferent(array $prices)
    {
        if (count($prices) === 0) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidArgumentException('Array can not be empty.');
        }

        $firstPrice = array_pop($prices);
        /* @var $firstPrice \Shopsys\FrameworkBundle\Model\Pricing\Price */
        foreach ($prices as $price) {
            if (!$price->getPriceWithoutVat()->equals($firstPrice->getPriceWithoutVat())
                || !$price->getPriceWithVat()->equals($firstPrice->getPriceWithVat())
            ) {
                return true;
            }
        }

        return false;
    }
}
