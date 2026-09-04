<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\AvailableForRegisteredCustomerUserOnly;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\InvalidPromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\LimitNotReachedException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NoLongerValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NotAvailableForCustomerUserPricingGroup;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\NotYetValidPromoCodeDateTimeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeWithoutRelationWithAnyProductFromCurrentCartException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PromoCodeValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
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

        $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($promoCodeCode, $this->domain->getId());

        if ($promoCode === null) {
            $this->addViolationWithCodeToContext($constraint->invalidMessage, PromoCode::INVALID_ERROR);

            return;
        }

        $this->validatePromoCode($promoCodeCode, $cart, $constraint);
    }

    protected function validatePromoCode(string $promoCodeCode, Cart $cart, PromoCode $constraint): void
    {
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
        } elseif ($cart->getAllAppliedPromoCodes() !== []) {
            $this->addViolationWithCodeToContext($constraint->onlySinglePromoCodeAllowedMessage, PromoCode::ONLY_SINGLE_PROMO_CODE_ALLOWED_ERROR);
        }
    }

    protected function addViolationWithCodeToContext(string $message, string $code): void
    {
        $this->context->buildViolation($message)
            ->setCode($code)
            ->atPath('promoCode')
            ->addViolation();
    }
}
