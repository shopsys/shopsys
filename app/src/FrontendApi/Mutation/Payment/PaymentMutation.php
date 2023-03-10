<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Payment;

use App\FrontendApi\Model\Order\OrderFacade;
use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\Model\Payment\Service\PaymentServiceFacade;
use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Throwable;

class PaymentMutation extends AbstractMutation
{
    /**
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     */
    public function __construct(
        private readonly OrderFacade $orderFacade,
        private readonly PaymentServiceFacade $paymentServiceFacade
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Payment\PaymentSetupCreationData
     */
    public function payOrderMutation(Argument $argument): PaymentSetupCreationData
    {
        try {
            $uuid = $argument['orderUuid'];
            $order = $this->orderFacade->getByUuid($uuid);

            return $this->paymentServiceFacade->payOrder($order);
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function checkPaymentStatusMutation(Argument $argument): bool
    {
        try {
            $uuid = $argument['orderUuid'];
            $order = $this->orderFacade->getByUuid($uuid);

            $this->paymentServiceFacade->updatePaymentTransactionsByOrder($order);

            return $order->isPaid();
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, [], null, $exception);
        }
    }
}
