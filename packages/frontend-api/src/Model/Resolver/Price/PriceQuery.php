<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrontendApiBundle\Model\Price\PriceFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PriceQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade
     */
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly PriceFacade $priceFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function priceByProductQuery($data): ProductPrice
    {
        if ($data instanceof Product) {
            return $this->productCachedAttributesFacade->getProductSellingPrice($data);
        }

        return $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function priceByPaymentQuery(Payment $payment): Price
    {
        return $this->paymentPriceCalculation->calculateIndependentPrice(
            $payment,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function priceByTransportQuery(Transport $transport): Price
    {
        return $this->transportPriceCalculation->calculateIndependentPrice(
            $transport,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
        );
    }
}
