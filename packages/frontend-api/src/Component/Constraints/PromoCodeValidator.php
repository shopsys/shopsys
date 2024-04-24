<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\AvailableForRegisteredCustomerUserOnly;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\LimitNotReachedException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NotAvailableForCustomerUserPricingGroup;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PromoCodeValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly CartApiFacade $cartApiFacade,
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
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

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
    protected function addViolationWithCodeToContext(string $message, string $code): void
    {
        $this->context->buildViolation($message)
            ->setCode($code)
            ->atPath('promoCode')
            ->addViolation();
    }
}
