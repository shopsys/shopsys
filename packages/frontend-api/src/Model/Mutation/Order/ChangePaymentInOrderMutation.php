<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Payment\Exception\PaymentNotFoundUserError;

class ChangePaymentInOrderMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly PaymentFacade $paymentFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function changePaymentInOrderMutation(Argument $argument): Order
    {
        $input = $argument['input'];
        $orderUuid = $input['orderUuid'];
        $paymentUuid = $input['paymentUuid'];

        try {
            $order = $this->orderFacade->getByUuid($orderUuid);
            $payment = $this->paymentFacade->getByUuid($paymentUuid);

            $this->orderFacade->changeOrderPayment($order, $payment);
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError('Order with UUID \'' . $orderUuid . '\' not found.');
        } catch (PaymentNotFoundException) {
            throw new PaymentNotFoundUserError('Payment with UUID \'' . $paymentUuid . '\' not found.');
        }

        return $order;
    }
}
