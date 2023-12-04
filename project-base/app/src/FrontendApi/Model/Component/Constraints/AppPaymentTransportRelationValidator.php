<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelation;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelationValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * The class need the prefix because of the conflicting name in GraphQL generated classes
 *
 * @see https://github.com/overblog/GraphQLBundle/issues/863
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @property \App\Model\Transport\TransportFacade $transportFacade
 */
class AppPaymentTransportRelationValidator extends PaymentTransportRelationValidator
{
    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        TransportFacade $transportFacade,
        private CurrentCustomerUser $currentCustomerUser,
        private CartFacade $cartFacade,
    ) {
        parent::__construct($paymentFacade, $transportFacade);
    }

    /**
     * Overriding the "config.validation" in the type definition does not prevent the original validation from being applied.
     * To prevent the original validation, the original validator class must be overridden in services.yaml
     *
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentTransportRelation) {
            throw new UnexpectedTypeException($constraint, PaymentTransportRelation::class);
        }
        $cartUuid = $value->cartUuid;
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $transportInCart = $cart->getTransport();
        $paymentInCart = $cart->getPayment();

        if ($transportInCart === null || $paymentInCart === null) {
            return;
        }

        $relationExists = in_array($transportInCart, $paymentInCart->getTransports(), true);

        if (!$relationExists) {
            $this->context->buildViolation($constraint->invalidCombinationMessage)
                ->setCode(PaymentTransportRelation::INVALID_COMBINATION_ERROR)
                ->addViolation();
        }
    }
}
