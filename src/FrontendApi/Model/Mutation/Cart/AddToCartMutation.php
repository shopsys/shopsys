<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Mutation\Cart;

use App\FrontendApi\Model\Cart\AddToCartFacade;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;

class AddToCartMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \App\FrontendApi\Model\Cart\AddToCartFacade
     */
    private AddToCartFacade $addToCartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @param \App\FrontendApi\Model\Cart\AddToCartFacade $addToCartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        AddToCartFacade $addToCartFacade,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->addToCartFacade = $addToCartFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return string
     */
    public function addToCart(Argument $argument, InputValidator $validator): string
    {
        $validator->validate();

        $input = $argument['input'];

        $productUuid = $input['productUuid'];
        $quantity = $input['quantity'];
        $cartUuid = $input['cartUuid'];
        $isAbsoluteQuantity = $input['isAbsoluteQuantity'];

        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $cart = $this->addToCartFacade->getCart($customerUser, $cartUuid);

        $this->addToCartFacade->addProductByUuidToCart($productUuid, $quantity, $isAbsoluteQuantity, $cart);

        return $cart->getCartIdentifier();
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'addToCart' => 'add_to_cart',
        ];
    }
}
