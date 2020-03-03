<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\OrderData;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Product\Type\ProductType;
use App\Model\Product\Type\ProductTypeFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

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
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     */
    public function __construct(
        OrderPreviewFactory $orderPreviewFactory,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        ProductTypeFacade $productTypeFacade
    ) {
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->productTypeFacade = $productTypeFacade;
    }

    /**
     * @param \App\Model\Order\OrderData|null $orderData
     * @return \App\Model\Order\Preview\SplitOrderPreview
     */
    public function createSplitOrderPreviewForCurrentCustomer(?OrderData $orderData): SplitOrderPreview
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());
        $promoCodeDiscountPercent = $this->findAppliedPromoCodePercentDiscount();
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();

        $transport = null;
        $payment = null;
        if ($orderData !== null) {
            $transport = $orderData->transport;
            $payment = $orderData->payment;
        }

        $orderPreviews = [];
        foreach ($this->productTypeFacade->getAll() as $productType) {
            $productTypeQuantifiedProducts = $this->filterQuantifiedProductsByProductType($quantifiedProducts, $productType);
            if (count($productTypeQuantifiedProducts) > 0) {
                $orderPreviews[] = $this->orderPreviewFactory->create(
                    $currency,
                    $this->domain->getId(),
                    $productTypeQuantifiedProducts,
                    $transport,
                    $payment,
                    $this->currentCustomerUser->findCurrentCustomerUser(),
                    $promoCodeDiscountPercent,
                    $productType
                );
            }
        }

        return new SplitOrderPreview($orderPreviews);
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    private function filterQuantifiedProductsByProductType(array $quantifiedProducts, ProductType $productType): array
    {
        $filtered = [];
        foreach ($quantifiedProducts as $index => $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();
            if ($product->getProductType() === $productType) {
                $filtered[$index] = $quantifiedProduct;
            }
        }

        return $filtered;
    }
}
