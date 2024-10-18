<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Customer\LastOrderDetail;
use Shopsys\ConvertimBundle\Model\Order\Exception\OrderDetailNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class OrderDetailFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\ConvertimBundle\Model\Order\OrderRepository $orderRepository
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly OrderRepository $orderRepository,
    ) {
    }

    /**
     * @param string $email
     * @return \Convertim\Customer\LastOrderDetail
     */
    public function createLastOrderDetail(string $email): LastOrderDetail
    {
        $order = $this->orderRepository->findLastOrderByEmailAndDomainId($email, $this->domain->getId());

        if ($order === null) {
            throw new OrderDetailNotFoundException($email);
        }

        return new LastOrderDetail(
            $order->getUuid(),
            $order->getTelephone(),
        );
    }
}
