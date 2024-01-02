<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Price;

use App\FrontendApi\Resolver\Price\Exception\ProductPriceMissingUserError;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceQuery as BasePriceQuery;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade, \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory, \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price calculateIndependentPaymentPrice(\App\Model\Payment\Payment $payment)
 */
class PriceQuery extends BasePriceQuery
{
    /**
     * {@inheritdoc}
     */
    public function priceByProductQuery($data): ProductPrice
    {
        if ($data instanceof Product) {
            $productPrice = $this->productCachedAttributesFacade->getProductSellingPrice($data);
        } else {
            $productPrice = $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
        }

        if ($productPrice === null) {
            throw new ProductPriceMissingUserError('The product price is not set.');
        }

        return $productPrice;
    }
}
