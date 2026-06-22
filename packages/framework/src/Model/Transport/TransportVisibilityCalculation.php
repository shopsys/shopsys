<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

class TransportVisibilityCalculation
{
    public function __construct(
        protected readonly IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation,
        protected readonly IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation,
        protected readonly TransportRepository $transportRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $allPaymentsOnDomain
     */
    public function isVisible(Transport $transport, array $allPaymentsOnDomain, int $domainId): bool
    {
        if (!$this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, $domainId)) {
            return false;
        }

        return $this->existsIndependentlyVisiblePaymentWithTransport($allPaymentsOnDomain, $transport, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     */
    protected function existsIndependentlyVisiblePaymentWithTransport(
        array $payments,
        Transport $transport,
        int $domainId,
    ): bool {
        foreach ($payments as $payment) {
            if ($this->independentPaymentVisibilityCalculation->isIndependentlyVisible($payment, $domainId)) {
                if (in_array($transport, $payment->getTransports(), true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $visiblePaymentsOnDomain
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function filterVisible(array $transports, array $visiblePaymentsOnDomain, int $domainId): array
    {
        $visibleTransports = [];

        foreach ($transports as $transport) {
            if ($this->isVisible($transport, $visiblePaymentsOnDomain, $domainId)) {
                $visibleTransports[] = $transport;
            }
        }

        return $visibleTransports;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function filterTransportsByProductsInCart(array $transports, Cart $cart): array
    {
        $excludedTransportIds = $this->getExcludedTransportIdsByProductsInCart($cart);

        return array_filter($transports, static fn (Transport $transport) => !in_array($transport->getId(), $excludedTransportIds, true));
    }

    /**
     * @return int[]
     */
    protected function getExcludedTransportIdsByProductsInCart(Cart $cart): array
    {
        return array_keys($this->getProductIdsIndexedByExcludedTransportId($cart));
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]>
     */
    public function getExcludingProductsByTransportIdForCart(Cart $cart): array
    {
        $productsById = [];

        foreach ($cart->getProducts() as $product) {
            $productsById[$product->getId()] = $product;
        }

        $excludingProductsByTransportId = [];

        foreach ($this->getProductIdsIndexedByExcludedTransportId($cart) as $transportId => $productIds) {
            foreach ($productIds as $productId) {
                if (isset($productsById[$productId])) {
                    $excludingProductsByTransportId[$transportId][] = $productsById[$productId];
                }
            }
        }

        return $excludingProductsByTransportId;
    }

    /**
     * @return array<int, int[]>
     */
    protected function getProductIdsIndexedByExcludedTransportId(Cart $cart): array
    {
        $productIds = array_map(static fn (Product $product) => $product->getId(), $cart->getProducts());

        return $this->transportRepository->getProductIdsIndexedByExcludedTransportId($productIds);
    }
}
