<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Payment;

use App\FrontendApi\Model\Order\OrderApiFacade;
use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\FrontendApi\Mutation\Payment\Exception\MaxTransactionCountReachedUserError;
use App\FrontendApi\Mutation\Payment\Exception\OrderAlreadyPaidUserError;
use App\Model\Payment\Service\PaymentServiceFacade;
use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Throwable;

class PaymentMutation extends AbstractMutation
{
    /**
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
     * @param \App\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     */
    public function __construct(
        private readonly OrderApiFacade $orderApiFacade,
        private readonly PaymentServiceFacade $paymentServiceFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Payment\PaymentSetupCreationData
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
     * @return array{isPaid: bool, transactionCount: int}
     */
    public function updatePaymentStatusMutation(Argument $argument): array
    {
        try {
            $uuid = $argument['orderUuid'];
            $order = $this->orderApiFacade->getByUuid($uuid);

            $this->paymentServiceFacade->updatePaymentTransactionsByOrder($order);

            return [
                'isPaid' => $order->isPaid(),
                'transactionCount' => $order->getPaymentTransactionsCount(),
                'paymentType' => $order->getPayment()->getType(),
            ];
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }
}
