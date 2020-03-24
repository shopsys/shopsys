<?php

declare(strict_types=1);

namespace App\Model\Order\Preview;

use App\Model\Order\Preview\Exception\PaymentPriceNotFoundException;
use App\Model\Order\Preview\Exception\TransportPriceNotFoundException;
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
        if (array_key_exists($productType->getId(), $this->transportPricesByProductTypeIdAndTransportId) === false
            || array_key_exists($transport->getId(), $this->transportPricesByProductTypeIdAndTransportId[$productType->getId()]) === false
        ) {
            throw new TransportPriceNotFoundException($productType, $transport);
        }

        return $this->transportPricesByProductTypeIdAndTransportId[$productType->getId()][$transport->getId()];
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPaymentPrice(Payment $payment): Price
    {
        if (array_key_exists($payment->getId(), $this->paymentPricesByPaymentId) === false) {
            throw new PaymentPriceNotFoundException($payment);
        }

        return $this->paymentPricesByPaymentId[$payment->getId()];
    }
}
