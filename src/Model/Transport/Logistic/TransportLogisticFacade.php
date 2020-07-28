<?php

declare(strict_types=1);

namespace App\Model\Transport\Logistic;

use App\Component\Domain\Domain;
use App\Model\Cart\CartFacade;
use App\Model\Payment\PaymentFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;

class TransportLogisticFacade
{
    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @var \App\Model\Transport\TransportFacade
     */
    private TransportFacade $transportFacade;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        TransportFacade $transportFacade,
        CartFacade $cartFacade,
        Domain $domain
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->transportFacade = $transportFacade;
        $this->cartFacade = $cartFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Transport\Transport[]
     */
    public function getAllowedTransportsForCurrentCart(): array
    {
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $transports = $this->transportFacade->getVisibleOnCurrentDomain($payments);
        $domainId = $this->domain->getId();

        foreach ($transports as $key => $transport) {
            if ($this->isTransportPossibleForQuantifiedProducts($transport, $quantifiedProducts, $domainId) === false) {
                unset($transports[$key]);
            }
        }

        return $transports;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param array $quantifiedProducts
     * @param int $domainId
     * @return bool
     */
    private function isTransportPossibleForQuantifiedProducts(Transport $transport, array $quantifiedProducts, int $domainId): bool
    {
        if ($transport->getType() === Transport::TYPE_COMMON) {
            return true;
        }

        $existsProductWhichIsNotSupportPackageTransport = $this->existsProductWhichIsNotSupportPackageTransport($quantifiedProducts, $domainId);

        if ($existsProductWhichIsNotSupportPackageTransport === true) {
            return $transport->getType() === Transport::TYPE_PALLET;
        }

        return $transport->getType() === Transport::TYPE_PACKAGE;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param int $domainId
     * @return bool
     */
    private function existsProductWhichIsNotSupportPackageTransport(array $quantifiedProducts, int $domainId): bool
    {
        foreach ($quantifiedProducts as $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();
            if ($product->canBeShippedAsPackage($domainId) === false) {
                return true;
            }
        }

        return false;
    }
}
