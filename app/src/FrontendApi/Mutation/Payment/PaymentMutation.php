<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Payment;

use App\FrontendApi\Model\Order\OrderFacade;
use App\FrontendApi\Model\Payment\PaymentSetupCreationData;
use App\Model\Payment\Service\PaymentServiceFacade;
use GraphQL\Error\Error;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Throwable;

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
        try {
            $uuid = $argument['orderUuid'];
            $order = $this->orderFacade->getByUuid($uuid);

            return $this->paymentServiceFacade->payOrder($order);
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, null, null, $exception);
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return bool
     */
    public function checkPaymentStatus(Argument $argument): bool
    {
        try {
            $uuid = $argument['orderUuid'];
            $order = $this->orderFacade->getByUuid($uuid);

            $this->paymentServiceFacade->updatePaymentTransactionsByOrder($order);

            return $order->isPaid();
        } catch (Throwable $exception) {
            throw new Error($exception->getMessage(), null, null, null, null, $exception);
        }
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
