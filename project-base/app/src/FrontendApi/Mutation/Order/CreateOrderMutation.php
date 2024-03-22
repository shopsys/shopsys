<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation as BaseCreateOrderMutation;

/**
 * @property \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade
 * @property \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @method sendEmail(\App\Model\Order\Order $order)
 */
class CreateOrderMutation extends BaseCreateOrderMutation
{
    public const VALIDATION_GROUP_BEFORE_DEFAULT = 'beforeDefaultValidation';

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array|string[]
     */
    protected function computeValidationGroups(Argument $argument): array
    {
        return array_merge([self::VALIDATION_GROUP_BEFORE_DEFAULT], parent::computeValidationGroups($argument));
    }
}
