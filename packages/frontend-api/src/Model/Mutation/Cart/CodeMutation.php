<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Cart;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartGiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrontendApiBundle\Component\Constraints\GiftVoucher;
use Shopsys\FrontendApiBundle\Component\Constraints\PromoCode;
use Shopsys\FrontendApiBundle\Model\Cart\ApplyCodeToCartRateLimiter;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeMutation extends AbstractMutation
{
    public function __construct(
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartWatcherFacade $cartWatcherFacade,
        protected readonly CartPromoCodeFacade $cartPromoCodeFacade,
        protected readonly CartGiftVoucherFacade $cartGiftVoucherFacade,
        protected readonly GiftVoucherFacade $giftVoucherFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly Domain $domain,
        protected readonly ApplyCodeToCartRateLimiter $applyCodeToCartRateLimiter,
        protected readonly ConstraintValidatorFactoryInterface $constraintValidatorFactory,
        protected readonly TranslatorInterface $translator,
    ) {
    }

    public function applyCodeToCartMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $this->applyCodeToCartRateLimiter->consume();

        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $code = $input['code'];

        if ($this->giftVoucherFacade->findByCode($code) !== null) {
            $cart = $this->applyCodeAsGiftVoucher($code, $cartUuid);
        } else {
            $cart = $this->applyCodeAsPromoCode($code, $cartUuid);
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    public function removeCodeFromCartMutation(
        Argument $argument,
        InputValidator $validator,
    ): CartWithModificationsResult {
        $validator->validate();

        $input = $argument['input'];

        $cartUuid = $input['cartUuid'];
        $code = $input['code'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        if ($cart->isGiftVoucherApplied($code)) {
            $this->cartGiftVoucherFacade->removeGiftVoucherByCode($cart, $code);

            return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
        }

        $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($code, $this->domain->getId());

        if ($promoCode !== null) {
            $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
        }

        return $this->cartWatcherFacade->getCheckedCartWithModifications($cart);
    }

    protected function applyCodeAsGiftVoucher(string $code, ?string $cartUuid): Cart
    {
        $this->validateWithConstraint(
            [
                'giftVoucherCode' => $code,
                'cartUuid' => $cartUuid,
            ],
            new GiftVoucher(),
        );

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $this->cartGiftVoucherFacade->applyGiftVoucherByCode($cart, $code);

        return $cart;
    }

    protected function applyCodeAsPromoCode(string $code, ?string $cartUuid): Cart
    {
        $this->validateWithConstraint(
            [
                'promoCode' => $code,
                'cartUuid' => $cartUuid,
            ],
            new PromoCode(),
        );

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);

        $this->cartPromoCodeFacade->applyPromoCodeByCode($cart, $code);

        return $cart;
    }

    /**
     * @param array<string, string|null> $validatedInput
     */
    protected function validateWithConstraint(array $validatedInput, Constraint $constraint): void
    {
        $violations = $this->createCustomerValidator()->startContext()
            ->atPath('input')
            ->validate((object)$validatedInput, $constraint)
            ->getViolations();

        if (count($violations) > 0) {
            throw new ArgumentsValidationException($violations);
        }
    }

    protected function createCustomerValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory($this->constraintValidatorFactory)
            ->setTranslator($this->translator)
            ->setTranslationDomain(Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN)
            ->getValidator();
    }
}
