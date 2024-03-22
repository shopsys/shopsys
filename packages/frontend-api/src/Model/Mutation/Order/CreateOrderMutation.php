<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory;
use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade;

class CreateOrderMutation extends AbstractMutation
{
    public const VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS = 'differentDeliveryAddress';
    public const VALIDATION_GROUP_ON_COMPANY_BEHALF = 'onCompanyBehalf';

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory $createOrderResultFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     */
    public function __construct(
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly PlaceOrderFacade $placeOrderFacade,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly CreateOrderResultFactory $createOrderResultFactory,
        protected readonly CartWatcherFacade $cartWatcherFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult
     */
    public function createOrderMutation(Argument $argument, InputValidator $validator): CreateOrderResult
    {
        $validationGroups = $this->computeValidationGroups($argument);
        $validator->validate($validationGroups);

        $orderData = $this->orderDataFactory->createOrderDataFromArgument($argument);

        $input = $argument['input'];
        $cartUuid = $input['cartUuid'];
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        if ($cartWithModifications->isCartModified()) {
            return $this->createOrderResultFactory->getCreateOrderResultByCartWithModifications(
                $cartWithModifications,
            );
        }

        $this->orderDataFactory->updateOrderDataFromCart($orderData, $cart);

        /** @var string|null $deliveryAddressUuid */
        $deliveryAddressUuid = $input['deliveryAddressUuid'];
        $deliveryAddress = $this->resolveDeliveryAddress($deliveryAddressUuid, $customerUser);

        $order = $this->placeOrderFacade->placeOrder(
            $orderData,
            $cart->getQuantifiedProducts(),
            $cart->getFirstAppliedPromoCode(),
            $deliveryAddress,
        );
        $this->cartApiFacade->deleteCart($cart);

        $this->sendEmail($order);

        return $this->createOrderResultFactory->getCreateOrderResultByOrder($order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function sendEmail(Order $order)
    {
        $this->orderMailFacade->sendEmail($order);
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

    /**
     * @param string|null $deliveryAddressUuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    protected function resolveDeliveryAddress(
        ?string $deliveryAddressUuid,
        ?CustomerUser $customerUser,
    ): ?DeliveryAddress {
        if ($deliveryAddressUuid === null || $customerUser === null) {
            return null;
        }

        return $this->deliveryAddressFacade->findByUuidAndCustomer(
            $deliveryAddressUuid,
            $customerUser->getCustomer(),
        );
    }
}
