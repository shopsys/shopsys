<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Transport\Logistic\TransportLogisticFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class PricesPreviewFacade
{
    /**
     * @var \App\Model\Transport\TransportPriceCalculation
     */
    protected $transportPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private $paymentPriceCalculation;

    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \App\Model\Transport\Logistic\TransportLogisticFacade
     */
    private TransportLogisticFacade $transportLogisticFacade;

    /**
     * @param \App\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Transport\Logistic\TransportLogisticFacade $transportLogisticFacade
     */
    public function __construct(
        TransportPriceCalculation $transportPriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        TransportLogisticFacade $transportLogisticFacade
    ) {
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->transportFacade = $transportFacade;
        $this->paymentFacade = $paymentFacade;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->transportLogisticFacade = $transportLogisticFacade;
    }

    /**
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @return \App\Model\Order\Preview\TransportAndPaymentPricesPreview
     */
    public function createTransportAndPaymentPricesPreviewForCurrentCustomer(
        SplitOrderPreview $splitOrderPreview
    ): TransportAndPaymentPricesPreview {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $paymentPricesByPaymentId = $this->paymentPriceCalculation->getCalculatedPricesIndexedByPaymentId(
            $payments,
            $currency,
            $splitOrderPreview->getProductsPrice(),
            $domainId
        );

        $transports = $this->transportFacade->getVisibleOnCurrentDomain($payments);
        $transports = $this->transportLogisticFacade->filterAllowedTransportsForCurrentCart($transports);
        $transportPricesByProductTypeIdAndTransportId = [];
        foreach ($splitOrderPreview->getOrderPreviews() as $orderPreview) {
            $productTypeId = $orderPreview->getProductType()->getId();
            $quantifiedProducts = $orderPreview->getQuantifiedProducts();
            $transportPricesByProductTypeIdAndTransportId[$productTypeId] = $this->transportPriceCalculation
                ->getCalculatedPricesIndexedByTransportIdByFreeTransport(
                    $transports,
                    $currency,
                    $domainId,
                    $quantifiedProducts,
                    $orderPreview->isTransportForFree()
                );
        }

        return new TransportAndPaymentPricesPreview(
            $transportPricesByProductTypeIdAndTransportId,
            $paymentPricesByPaymentId
        );
    }
}
