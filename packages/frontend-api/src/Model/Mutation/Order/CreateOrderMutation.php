<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetDeliveryAddressByDeliveryAddressUuidMiddleware;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult;
use Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory;
use Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory;

class CreateOrderMutation extends AbstractMutation
{
    public const string VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED = 'isDeliveryAddressDifferentFromBillingWithoutPreselected';
    public const string VALIDATION_GROUP_ON_COMPANY_BEHALF = 'onCompanyBehalf';
    public const string VALIDATION_GROUP_ANONYMOUS_USER = 'anonymousUser';

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory $createOrderResultFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     */
    public function __construct(
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CreateOrderResultFactory $createOrderResultFactory,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly Domain $domain,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly PlaceOrderFacade $placeOrderFacade,
        protected readonly OrderInputFactory $orderInputFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult
     */
    public function createOrderMutation(Argument $argument, InputValidator $validator): CreateOrderResult
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $validationGroups = $this->computeValidationGroups($argument, $customerUser);
        $validator->validate($validationGroups);

        $orderData = $this->orderDataFactory->createOrderDataFromArgument($argument);

        $input = $argument['input'];
        $cartUuid = $input['cartUuid'];
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $cartWithModifications = $this->cartWatcherFacade->getCheckedCartWithModifications($cart);

        if ($cartWithModifications->isCartModified()) {
            return $this->createOrderResultFactory->getCreateOrderResultByCartWithModifications(
                $cartWithModifications,
            );
        }

        /** @var string|null $deliveryAddressUuid */
        $deliveryAddressUuid = $input['deliveryAddressUuid'];

        $orderInput = $this->orderInputFactory->createFromCart($cart, $this->domain->getCurrentDomainConfig());
        $orderInput->addAdditionalData(SetDeliveryAddressByDeliveryAddressUuidMiddleware::DELIVERY_ADDRESS_UUID, $deliveryAddressUuid);

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        $order = $this->placeOrderFacade->placeOrder($orderData, $deliveryAddressUuid);

        $this->cartApiFacade->deleteCart($cart);

        return $this->createOrderResultFactory->getCreateOrderResultByOrder($order);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $currentCustomerUser
     * @return string[]
     */
    protected function computeValidationGroups(Argument $argument, ?CustomerUser $currentCustomerUser): array
    {
        $input = $argument['input'];
        $validationGroups = ['Default'];

        if ($input[self::VALIDATION_GROUP_ON_COMPANY_BEHALF] === true) {
            $validationGroups[] = self::VALIDATION_GROUP_ON_COMPANY_BEHALF;
        }

        if ($input['isDeliveryAddressDifferentFromBilling'] === true && $input['deliveryAddressUuid'] === null) {
            $validationGroups[] = self::VALIDATION_GROUP_IS_DELIVERY_ADDRESS_DIFFERENT_FROM_BILLING_WITHOUT_PRESELECTED;
        }

        if ($currentCustomerUser === null) {
            $validationGroups[] = self::VALIDATION_GROUP_ANONYMOUS_USER;
        }

        return $validationGroups;
    }
}
