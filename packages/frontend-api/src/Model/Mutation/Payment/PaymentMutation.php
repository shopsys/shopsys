<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Payment;

use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception\MaxTransactionCountReachedUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception\OrderAlreadyPaidUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception\OrderWaitingForProcessPaymentUserError;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\OrderPaymentPageContentCache;
use Shopsys\FrontendApiBundle\Model\Order\PaymentContentPage\PaymentContentPage;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderSentPageNotAvailableUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderSentPageContentQuery;
use Throwable;

class PaymentMutation extends AbstractMutation
{
    public function __construct(
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderSentPageContentQuery $orderSentPageContentQuery,
        protected readonly OrderPaymentPageContentCache $orderPaymentPageContentCache,
    ) {
    }

    public function payOrderMutation(Argument $argument): PaymentSetupCreationData
    {
        $uuid = $argument['orderUuid'];
        $order = $this->orderApiFacade->getByUuid($uuid);

        if ($order->isPaid()) {
            throw new OrderAlreadyPaidUserError('Order is already paid');
        }

        if ($order->hasPaymentInProcess()) {
            throw new OrderWaitingForProcessPaymentUserError('Order is awaiting payment verification.');
        }

        if ($order->isMaxTransactionCountReached()) {
            throw new MaxTransactionCountReachedUserError('Max transaction count reached');
        }

        try {
            $order->resetOrderPaymentStatusPageValidityHash();

            $paymentSetupCreationData = $this->paymentServiceFacade->payOrder($order);
            // Attach the freshly generated validity hash to the response so the frontend
            // can redirect to the payment status page without an extra API call
            $paymentSetupCreationData->setOrderPaymentStatusPageValidityHash($order->getOrderPaymentStatusPageValidityHash());

            return $paymentSetupCreationData;
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }

    public function updatePaymentStatusMutation(Argument $argument): Order
    {
        try {
            $uuid = $argument['orderUuid'];
            $orderPaymentStatusPageValidityHash = $argument['orderPaymentStatusPageValidityHash'] ?? null;
            $order = $this->orderApiFacade->getByUuid($uuid);

            if ($this->paymentServiceFacade->updatePaymentTransactionsByOrder($order)) {
                $this->orderFacade->updatePaymentByLastPaymentTransaction($order);
            }

            // Matching hash proves the request came from a legitimate PayOrder return URL.
            // Opens the time-limited window for payment status page content.
            if ($orderPaymentStatusPageValidityHash !== null && $order->getOrderPaymentStatusPageValidityHash() === $orderPaymentStatusPageValidityHash) {
                $this->orderFacade->setOrderPaymentStatusPageValidFromNow($order);
            }

            $this->orderPaymentPageContentCache->setForOrderUuid(
                $order->getUuid(),
                $this->getPaymentPageContentOrNull($order),
            );

            return $order;
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }

    protected function getPaymentPageContentOrNull(
        Order $order,
    ): ?PaymentContentPage {
        try {
            return $this->orderSentPageContentQuery->getOrderPaymentPageContentByOrder($order);
        } catch (OrderSentPageNotAvailableUserError) {
            return null;
        }
    }
}
