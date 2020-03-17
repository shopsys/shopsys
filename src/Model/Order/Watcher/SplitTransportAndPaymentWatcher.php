<?php

declare(strict_types=1);

namespace App\Model\Order\Watcher;

use App\Component\Pricing\PricingUtils;
use App\Model\Order\FrontOrderData;
use App\Model\Order\Preview\Exception\PaymentPriceNotFoundException;
use App\Model\Order\Preview\Exception\TransportPriceNotFoundException;
use App\Model\Order\Preview\SplitOrderPreview;
use App\Model\Order\Preview\TransportAndPaymentPricesPreview;
use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentCheckResult;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SplitTransportAndPaymentWatcher
{
    private const SESSION_ROOT = 'split_transport_and_payment_watcher';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @return \Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentCheckResult
     */
    public function checkTransportsAndPaymentBySplitOrderPreview(
        FrontOrderData $frontOrderData,
        SplitOrderPreview $splitOrderPreview
    ): TransportAndPaymentCheckResult {
        $transportsByProductTypeId = $frontOrderData->transportsByProductTypeId;
        $payment = $frontOrderData->payment;
        $paymentPriceChanged = false;
        $transportPriceChanged = false;

        /** @var \App\Model\Order\Preview\TransportAndPaymentPricesPreview $rememberedTransportAndPaymentPricesPreview */
        $rememberedTransportAndPaymentPricesPreview = $this->session->get(self::SESSION_ROOT);
        $actualTransportAndPaymentPricesPreview = $splitOrderPreview->getTransportAndPaymentPricesPreview();

        if ($rememberedTransportAndPaymentPricesPreview !== null) {
            $transportPriceChanged = $this->isTransportPriceChanged(
                $splitOrderPreview->getOrderPreviews(),
                $transportsByProductTypeId,
                $rememberedTransportAndPaymentPricesPreview,
                $actualTransportAndPaymentPricesPreview
            );

            if ($payment !== null) {
                $paymentPriceChanged = $this->isPaymentPriceChanged(
                    $actualTransportAndPaymentPricesPreview,
                    $rememberedTransportAndPaymentPricesPreview,
                    $payment
                );
            }
        }

        $this->session->set(self::SESSION_ROOT, $actualTransportAndPaymentPricesPreview);

        return new TransportAndPaymentCheckResult($transportPriceChanged, $paymentPriceChanged);
    }

    /**
     * @param \App\Model\Order\Preview\OrderPreview[] $orderPreviews
     * @param \App\Model\Transport\Transport[]|null[] $transportsByProductTypeId
     * @param \App\Model\Order\Preview\TransportAndPaymentPricesPreview $rememberedTransportAndPaymentPricesPreview
     * @param \App\Model\Order\Preview\TransportAndPaymentPricesPreview $actualTransportAndPaymentPricesPreview
     * @return bool
     */
    private function isTransportPriceChanged(
        array $orderPreviews,
        array $transportsByProductTypeId,
        TransportAndPaymentPricesPreview $rememberedTransportAndPaymentPricesPreview,
        TransportAndPaymentPricesPreview $actualTransportAndPaymentPricesPreview
    ): bool {
        foreach ($orderPreviews as $orderPreview) {
            $productType = $orderPreview->getProductType();
            if (array_key_exists($productType->getId(), $transportsByProductTypeId) === true) {
                $transport = $transportsByProductTypeId[$productType->getId()];
                if ($transport !== null) {
                    try {
                        $rememberedTransportPrice = $rememberedTransportAndPaymentPricesPreview->getTransportPrice($transport, $productType);
                        $actualTransportPrice = $actualTransportAndPaymentPricesPreview->getTransportPrice($transport, $productType);

                        if (PricingUtils::equals($rememberedTransportPrice, $actualTransportPrice) === false) {
                            return true;
                        }
                    } catch (TransportPriceNotFoundException $exception) {
                        // It's okay, remembered prices preview may not contain new selected transport
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param \App\Model\Order\Preview\TransportAndPaymentPricesPreview $actualTransportAndPaymentPricesPreview
     * @param \App\Model\Order\Preview\TransportAndPaymentPricesPreview $rememberedTransportAndPaymentPricesPreview
     * @param \App\Model\Payment\Payment $payment
     * @return bool
     */
    private function isPaymentPriceChanged(
        TransportAndPaymentPricesPreview $actualTransportAndPaymentPricesPreview,
        TransportAndPaymentPricesPreview $rememberedTransportAndPaymentPricesPreview,
        Payment $payment
    ): bool {
        try {
            $rememberedPaymentPrice = $rememberedTransportAndPaymentPricesPreview->getPaymentPrice($payment);
            $actualPaymentPrice = $actualTransportAndPaymentPricesPreview->getPaymentPrice($payment);

            return PricingUtils::equals($rememberedPaymentPrice, $actualPaymentPrice) === false;
        } catch (PaymentPriceNotFoundException $exception) {
            // It's okay, remembered prices preview may not contain new selected payment
            return false;
        }
    }
}
