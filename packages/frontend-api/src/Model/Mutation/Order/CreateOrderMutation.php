<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory;
use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade;

class CreateOrderMutation implements MutationInterface, AliasedInterface
{
    public const VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS = 'differentDeliveryAddress';
    public const VALIDATION_GROUP_ON_COMPANY_BEHALF = 'onCompanyBehalf';

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory
     */
    protected $orderDataFactory;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade
     */
    protected $placeOrderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade
     */
    protected $orderMailFacade;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     */
    public function __construct(
        OrderDataFactory $orderDataFactory,
        PlaceOrderFacade $placeOrderFacade,
        OrderMailFacade $orderMailFacade
    ) {
        $this->orderDataFactory = $orderDataFactory;
        $this->placeOrderFacade = $placeOrderFacade;
        $this->orderMailFacade = $orderMailFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrder(Argument $argument, InputValidator $validator): Order
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $orderData = $this->orderDataFactory->createOrderDataFromArgument($argument);
        $quantifiedProducts = $this->orderDataFactory->createQuantifiedProductsFromArgument($argument);

        $order = $this->placeOrderFacade->placeOrder($orderData, $quantifiedProducts);

        try {
            $this->sendEmail($order);
        } catch (\Shopsys\FrameworkBundle\Model\Mail\Exception\MailException $e) {
            throw new UserError('Unable to send some emails, please contact us for order verification.');
        }

        return $order;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'createOrder' => 'create_order',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function sendEmail(Order $order)
    {
        $mailTemplate = $this->orderMailFacade->getMailTemplateByStatusAndDomainId($order->getStatus(), $order->getDomainId());
        if ($mailTemplate->isSendMail()) {
            $this->orderMailFacade->sendEmail($order);
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string[]
     */
    protected function computeValidationGroups(Argument $argument): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input[self::VALIDATION_GROUP_ON_COMPANY_BEHALF] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_ON_COMPANY_BEHALF;
        }

        if ($input[self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS;
        }

        return $validationGroups;
    }
}
