<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Payment\Exception\PaymentNotFoundUserError;

class ChangePaymentInOrderMutation extends AbstractMutation
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderApiFacade $orderApiFacade,
    ) {
    }

    public function changePaymentInOrderMutation(Argument $argument): Order
    {
        $input = $argument['input'];
        $paymentUuid = $input['paymentUuid'];

        $order = $this->orderApiFacade->getAuthorizedOrder($input['orderUuid'], $input['orderUrlHash']);

        try {
            $payment = $this->paymentFacade->getByUuid($paymentUuid);

            $this->orderFacade->changeOrderPayment($order, $payment);
        } catch (PaymentNotFoundException) {
            throw new PaymentNotFoundUserError('Payment with UUID \'' . $paymentUuid . '\' not found.');
        }

        return $order;
    }
}
