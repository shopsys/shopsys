<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Payment\Payment;
use App\Model\Product\Type\ProductType;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class TransportAndPaymentPricesPreview
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price[][]
     */
    private $transportPricesByProductTypeIdAndTransportId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    private $paymentPricesByPaymentId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[][] $transportPricesByProductTypeIdAndTransportId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $paymentPricesByPaymentId
     */
    public function __construct(array $transportPricesByProductTypeIdAndTransportId, array $paymentPricesByPaymentId)
    {
        $this->transportPricesByProductTypeIdAndTransportId = $transportPricesByProductTypeIdAndTransportId;
        $this->paymentPricesByPaymentId = $paymentPricesByPaymentId;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Product\Type\ProductType $productType
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTransportPrice(Transport $transport, ProductType $productType): Price
    {
        return $this->transportPricesByProductTypeIdAndTransportId[$productType->getId()][$transport->getId()];
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPaymentPrice(Payment $payment): Price
    {
        return $this->paymentPricesByPaymentId[$payment->getId()];
    }
}
