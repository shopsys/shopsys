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
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Throwable;

class PaymentMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData
     */
    public function payOrderMutation(Argument $argument): PaymentSetupCreationData
    {
        $uuid = $argument['orderUuid'];
        $order = $this->orderApiFacade->getByUuid($uuid);

        if ($order->isPaid()) {
            throw new OrderAlreadyPaidUserError('Order is already paid');
        }

        if ($order->isMaxTransactionCountReached()) {
            throw new MaxTransactionCountReachedUserError('Max transaction count reached');
        }

        try {
            return $this->paymentServiceFacade->payOrder($order);
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function updatePaymentStatusMutation(Argument $argument): Order
    {
        try {
            $uuid = $argument['orderUuid'];
            $orderPaymentStatusPageValidityHash = $argument['orderPaymentStatusPageValidityHash'] ?? null;
            $order = $this->orderApiFacade->getByUuid($uuid);

            $this->paymentServiceFacade->updatePaymentTransactionsByOrder($order);

            if ($orderPaymentStatusPageValidityHash !== null && $order->getOrderPaymentStatusPageValidityHash() === $orderPaymentStatusPageValidityHash) {
                $this->orderFacade->setOrderPaymentStatusPageValidFromNow($order);
            }

            return $order;
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }
}
