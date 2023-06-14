<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\Exception\AvailableForRegisteredCustomerUserOnly;
use App\Model\Order\PromoCode\Exception\LimitNotReachedException;
use App\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\NotAvailableForCustomerUserPricingGroup;
use App\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use App\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PromoCodeValidator extends ConstraintValidator
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        private CurrentCustomerUser $currentCustomerUser,
        private CurrentPromoCodeFacade $currentPromoCodeFacade,
        private CartFacade $cartFacade,
    ) {
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PromoCode) {
            throw new UnexpectedTypeException($constraint, PromoCode::class);
        }
        $promoCodeCode = $value->promoCode;

        if ($promoCodeCode === null) {
            return;
        }
        $cartUuid = $value->cartUuid;
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        try {
            $this->currentPromoCodeFacade->getValidatedPromoCode($promoCodeCode, $cart);
        } catch (InvalidPromoCodeException $ex) {
            $this->addViolationWithCodeToContext($constraint->invalidMessage, PromoCode::INVALID_ERROR);
        } catch (NotYetValidPromoCodeDateTimeException $exception) {
            $this->addViolationWithCodeToContext($constraint->notYetValidMessage, PromoCode::NOT_YET_VALID_ERROR);
        } catch (NoLongerValidPromoCodeDateTimeException $exception) {
            $this->addViolationWithCodeToContext($constraint->noLongerValidMessage, PromoCode::NO_LONGER_VALID_ERROR);
        } catch (LimitNotReachedException $exception) {
            $this->addViolationWithCodeToContext($constraint->limitNotReachedMessage, PromoCode::LIMIT_NOT_REACHED_ERROR);
        } catch (PromoCodeWithoutRelationWithAnyProductFromCurrentCartException $exception) {
            $this->addViolationWithCodeToContext($constraint->noRelationToProductsInCartMessage, PromoCode::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR);
        } catch (AvailableForRegisteredCustomerUserOnly $exception) {
            $this->addViolationWithCodeToContext($constraint->forRegisteredCustomerUsersOnlyMessage, PromoCode::FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR);
        } catch (NotAvailableForCustomerUserPricingGroup $exception) {
            $this->addViolationWithCodeToContext($constraint->notAvailableForCustomerUserPricingGroupMessage, PromoCode::NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR);
        }

        if ($cart->isPromoCodeApplied($promoCodeCode)) {
            $this->addViolationWithCodeToContext($constraint->alreadyAppliedPromoCodeMessage, PromoCode::ALREADY_APPLIED_PROMO_CODE_ERROR);
        }
    }

    /**
     * @param string $message
     * @param string $code
     */
    private function addViolationWithCodeToContext(string $message, string $code): void
    {
        $this->context->buildViolation($message)
            ->setCode($code)
            ->atPath('promoCode')
            ->addViolation();
    }
}
