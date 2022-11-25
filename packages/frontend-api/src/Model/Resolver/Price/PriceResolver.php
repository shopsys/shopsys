<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
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

class PriceResolver implements QueryInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    protected $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    protected $transportPriceCalculation;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Price\PriceFacade
     */
    protected PriceFacade $priceFacade;

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
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        PaymentPriceCalculation $paymentPriceCalculation,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        TransportPriceCalculation $transportPriceCalculation,
        PriceFacade $priceFacade
    ) {
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->priceFacade = $priceFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array{prices: array<string, mixed>} $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function resolveByProduct(Product|array $data): ProductPrice
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
    public function resolveByPayment(Payment $payment): Price
    {
        return $this->paymentPriceCalculation->calculateIndependentPrice(
            $payment,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function resolveByTransport(Transport $transport): Price
    {
        return $this->transportPriceCalculation->calculateIndependentPrice(
            $transport,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId()
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'Price',
        ];
    }
}
