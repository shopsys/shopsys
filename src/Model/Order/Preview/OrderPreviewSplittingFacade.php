<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\OrderData;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Product\Type\ProductType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderPreviewSplittingFacade
{
    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation
     */
    private $orderPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private $paymentPriceCalculation;

    /**
     * @var \App\Model\Order\Preview\PricesPreviewFacade
     */
    private $pricesPreviewFacade;

    /**
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \App\Model\Order\Preview\PricesPreviewFacade $pricesPreviewFacade
     */
    public function __construct(
        OrderPreviewFactory $orderPreviewFactory,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        OrderPriceCalculation $orderPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        PricesPreviewFacade $pricesPreviewFacade
    ) {
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->orderPriceCalculation = $orderPriceCalculation;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->pricesPreviewFacade = $pricesPreviewFacade;
    }

    /**
     * @param \App\Model\Order\OrderData|null $orderData
     * @return \App\Model\Order\Preview\SplitOrderPreview
     */
    public function createSplitOrderPreviewForCurrentCustomer(?OrderData $orderData): SplitOrderPreview
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        $transportsByProductTypeId = [];
        $transportPersonalPickupStockByProductTypeId = [];
        $payment = null;
        if ($orderData !== null) {
            $transportsByProductTypeId = $orderData->transportsByProductTypeId;
            $transportPersonalPickupStockByProductTypeId = $orderData->transportPersonalPickupStockByProductTypeId ?? [];
            $payment = $orderData->payment;
        }

        $orderPreviews = $this->createOrderPreviewsWithProductType($currency, $transportsByProductTypeId, $transportPersonalPickupStockByProductTypeId, $this->domain->getId());

        $productsPrice = $this->sumProductsPrices($orderPreviews);
        $productsSalePrice = $this->sumProductsSalePrices($orderPreviews);
        $sumTotalPrices = $this->sumTotalPrices($orderPreviews);
        $roundingPrice = null;
        $totalPrice = $sumTotalPrices;

        $paymentPrice = null;
        if ($payment !== null) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $currency,
                $productsPrice,
                $this->domain->getId()
            );

            $sumTotalPrices = $sumTotalPrices->add($paymentPrice);
            $totalPrice = $totalPrice->add($paymentPrice);

            $roundingPrice = $this->orderPriceCalculation->calculateOrderRoundingPrice($payment, $currency, $sumTotalPrices);
            if ($roundingPrice !== null) {
                $totalPrice = $totalPrice->add($roundingPrice);
            }
        }

        $splitOrderPreview = new SplitOrderPreview($orderPreviews, $payment, $totalPrice, $productsPrice, $productsSalePrice, $roundingPrice);

        // optimization - prices for all transports and payments are not necessary when OrderData does not exists
        if ($orderData !== null) {
            $transportAndPaymentPricesPreview = $this->pricesPreviewFacade->createTransportAndPaymentPricesPreviewForCurrentCustomer(
                $splitOrderPreview
            );
            $splitOrderPreview->setTransportAndPaymentPricesPreview($transportAndPaymentPricesPreview);
        }

        return $splitOrderPreview;
    }

    /**
     * @return string|null
     */
    private function findAppliedPromoCodePercentDiscount(): ?string
    {
        $validEnteredPromoCode = $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull();
        if ($validEnteredPromoCode === null) {
            return null;
        }

        return $validEnteredPromoCode->getPercent();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Product\Type\ProductType $productType
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    private function filterQuantifiedProductsByProductType(array $quantifiedProducts, ProductType $productType, int $domainId): array
    {
        $filtered = [];
        foreach ($quantifiedProducts as $index => $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();
            if ($product->getProductType($domainId) === $productType) {
                $filtered[$index] = $quantifiedProduct;
            }
        }

        return $filtered;
    }

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function sumTotalPrices(array $orderPreviews): Price
    {
        $sumPrice = Price::zero();
        foreach ($orderPreviews as $orderPreview) {
            $sumPrice = $sumPrice->add($orderPreview->getTotalPrice());
        }

        return $sumPrice;
    }

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function sumProductsPrices(array $orderPreviews): Price
    {
        $sumPrice = Price::zero();
        foreach ($orderPreviews as $orderPreview) {
            $sumPrice = $sumPrice->add($orderPreview->getProductsPrice());
        }

        return $sumPrice;
    }

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function sumProductsSalePrices(array $orderPreviews): Price
    {
        $sumPrice = Price::zero();
        foreach ($orderPreviews as $orderPreview) {
            $sumPrice = $sumPrice->add($orderPreview->getSubHighAndLowPrice());
        }

        return $sumPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \App\Model\Transport\Transport[] $transportsByProductTypeId
     * @param \App\Model\Stock\Stock[] $transportPersonalPickupStockByProductTypeId
     * @param int $domainId
     * @return \App\Model\Order\Preview\OrderPreview[]
     */
    private function createOrderPreviewsWithProductType(Currency $currency, array $transportsByProductTypeId, array $transportPersonalPickupStockByProductTypeId, int $domainId): array
    {
        $promoCodeDiscountPercent = $this->findAppliedPromoCodePercentDiscount();
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();

        $productTypes = $this->getUsedProductTypesInCurrentCart();
        usort($productTypes, static function (ProductType $productType1, ProductType $productType2) {
            return $productType1->getPosition() <=> $productType2->getPosition();
        });

        $orderPreviews = [];
        foreach ($productTypes as $productType) {
            $productTypeQuantifiedProducts = $this->filterQuantifiedProductsByProductType($quantifiedProducts, $productType, $domainId);
            if (count($productTypeQuantifiedProducts) > 0) {
                $orderPreviews[] = $this->orderPreviewFactory->create(
                    $currency,
                    $this->domain->getId(),
                    $productTypeQuantifiedProducts,
                    $transportsByProductTypeId[$productType->getId()] ?? null,
                    null,
                    $this->currentCustomerUser->findCurrentCustomerUser(),
                    $promoCodeDiscountPercent,
                    $productType,
                    $transportPersonalPickupStockByProductTypeId[$productType->getId()] ?? null
                );
            }
        }

        return $orderPreviews;
    }

    /**
     * @return \App\Model\Product\Type\ProductType[]
     */
    public function getUsedProductTypesInCurrentCart(): array
    {
        $productTypes = [];

        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        foreach ($quantifiedProducts as $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();
            $productType = $product->getProductType($this->domain->getId());
            $productTypes[$productType->getId()] = $productType;
        }

        return $productTypes;
    }
}
