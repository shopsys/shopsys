<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

class TransportVisibilityCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly IndependentTransportVisibilityCalculation $independentTransportVisibilityCalculation,
        protected readonly IndependentPaymentVisibilityCalculation $independentPaymentVisibilityCalculation,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $allPaymentsOnDomain
     * @param int $domainId
     * @return bool
     */
    public function isVisible(Transport $transport, array $allPaymentsOnDomain, $domainId)
    {
        if (!$this->independentTransportVisibilityCalculation->isIndependentlyVisible($transport, $domainId)) {
            return false;
        }

        return $this->existsIndependentlyVisiblePaymentWithTransport($allPaymentsOnDomain, $transport, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param int $domainId
     * @return bool
     */
    protected function existsIndependentlyVisiblePaymentWithTransport(array $payments, Transport $transport, $domainId)
    {
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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function filterVisible(array $transports, array $visiblePaymentsOnDomain, $domainId)
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
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function filterTransportsByProductsInCart(array $transports, Cart $cart): array
    {
        $excludedTransportIds = $this->getExcludedTransportIdsByProductsInCart($cart);

        return array_filter($transports, static fn (Transport $transport) => !in_array($transport->getId(), $excludedTransportIds, true));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return int[]
     */
    protected function getExcludedTransportIdsByProductsInCart(Cart $cart): array
    {
        $productIds = array_map(static fn (Product $product) => $product->getId(), $cart->getProducts());

        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addScalarResult('transport_id', 'transport_id');

        $sql = 'SELECT DISTINCT transport_id FROM product_excluded_transports WHERE product_id IN (:productIds)';

        return array_column($this->entityManager->createNativeQuery($sql, $resultSetMapping)->execute(['productIds' => $productIds]), 'transport_id');
    }
}
