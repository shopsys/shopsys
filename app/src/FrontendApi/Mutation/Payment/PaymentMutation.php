<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Payment;

use App\FrontendApi\Model\Order\OrderFacade;
use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\Model\Payment\Service\PaymentServiceFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class PaymentMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Order\OrderFacade
     */
    private OrderFacade $orderFacade;

    /**
     * @var \App\Model\Payment\Service\PaymentServiceFacade
     */
    private PaymentServiceFacade $paymentServiceFacade;

    /**
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     */
    public function __construct(
        OrderFacade $orderFacade,
        PaymentServiceFacade $paymentServiceFacade
    ) {
        $this->orderFacade = $orderFacade;
        $this->paymentServiceFacade = $paymentServiceFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\FrontendApi\Model\Payment\PaymentSetupCreationData
     */
    public function payOrder(Argument $argument): PaymentSetupCreationData
    {
        $uuid = $argument['orderUuid'];
        $order = $this->orderFacade->getByUuid($uuid);

        return $this->paymentServiceFacade->payOrder($order);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function checkPaymentStatus(Argument $argument): bool
    {
        $uuid = $argument['orderUuid'];
        $order = $this->orderFacade->getByUuid($uuid);

        $this->paymentServiceFacade->updatePaymentTransactionsByOrder($order);

        return $order->isPaid();
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'payOrder' => 'payOrder',
            'checkPaymentStatus' => 'checkPaymentStatus',
        ];
    }
}
