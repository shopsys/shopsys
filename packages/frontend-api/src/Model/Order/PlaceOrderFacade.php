<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class PlaceOrderFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderProductFacade $orderProductFacade,
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CustomerUserFacade $customerUserFacade
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function placeOrder(OrderData $orderData, array $quantifiedProducts): Order
    {
        $orderData->status = $this->orderStatusRepository->getDefault();

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $orderPreview = $this->createOrderPreview(
            $quantifiedProducts,
            $orderData->transport,
            $orderData->payment,
            $customerUser
        );

        $order = $this->orderFacade->createOrder($orderData, $orderPreview, $customerUser);
        $this->orderProductFacade->subtractOrderProductsFromStock($order->getProductItems());

        if ($customerUser instanceof CustomerUser) {
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, null);
        }

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport|null $transport
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment|null $payment
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview
     */
    protected function createOrderPreview(array $quantifiedProducts, ?Transport $transport, ?Payment $payment, ?CustomerUser $customerUser): OrderPreview
    {
        return $this->orderPreviewFactory->create(
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
            $quantifiedProducts,
            $transport,
            $payment,
            $customerUser,
            null
        );
    }
}
