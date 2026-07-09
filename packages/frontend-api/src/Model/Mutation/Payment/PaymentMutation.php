<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Payment;

use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception\MaxTransactionCountReachedUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception\OrderAlreadyPaidUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Payment\Exception\OrderWaitingForProcessPaymentUserError;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\UpdatePaymentStatusResult;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderConfirmationPageContentQuery;
use Throwable;

class PaymentMutation extends AbstractMutation
{
    public function __construct(
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderConfirmationPageContentQuery $orderConfirmationPageContentQuery,
    ) {
    }

    public function payOrderMutation(Argument $argument): PaymentSetupCreationData
    {
        $order = $this->orderApiFacade->getAuthorizedOrder($argument['orderUuid'], $argument['orderUrlHash']);

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
            return $this->paymentServiceFacade->payOrder($order);
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }

    public function updatePaymentStatusMutation(Argument $argument): UpdatePaymentStatusResult
    {
        $order = $this->orderApiFacade->getAuthorizedOrder($argument['orderUuid'], $argument['orderUrlHash']);

        try {
            if ($this->paymentServiceFacade->updatePaymentTransactionsByOrder($order)) {
                $this->orderFacade->updatePaymentByLastPaymentTransaction($order);
            }

            return new UpdatePaymentStatusResult(
                $order,
                $this->orderConfirmationPageContentQuery->orderConfirmationPageContentQuery($order),
            );
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }
}
