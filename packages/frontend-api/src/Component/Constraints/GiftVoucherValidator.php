<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use DateTimeImmutable;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GiftVoucherValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly GiftVoucherFacade $giftVoucherFacade,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof GiftVoucher) {
            throw new UnexpectedTypeException($constraint, GiftVoucher::class);
        }
        $giftVoucherCode = $value->giftVoucherCode;

        if ($giftVoucherCode === null) {
            return;
        }
        $domainId = $this->domain->getId();

        $giftVoucher = $this->giftVoucherFacade->findByCode($giftVoucherCode);

        if ($giftVoucher === null
            || $giftVoucher->getDomainId() !== $domainId
            || $giftVoucher->getCurrencyCode() !== $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId)->getCode()
        ) {
            $this->addViolationWithCodeToContext($constraint->invalidMessage, GiftVoucher::INVALID_ERROR);

            return;
        }

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $value->cartUuid);

        if ($cart->isGiftVoucherApplied($giftVoucher->getCode())) {
            $this->addViolationWithCodeToContext($constraint->alreadyAppliedGiftVoucherMessage, GiftVoucher::ALREADY_APPLIED_GIFT_VOUCHER_ERROR);

            return;
        }

        if (!$giftVoucher->isUnredeemed()) {
            $this->addViolationWithCodeToContext($constraint->giftVoucherNotRedeemableMessage, GiftVoucher::GIFT_VOUCHER_NOT_REDEEMABLE_ERROR);

            return;
        }

        if (!$giftVoucher->isValidAt(new DateTimeImmutable())) {
            $this->addViolationWithCodeToContext($constraint->noLongerValidMessage, GiftVoucher::NO_LONGER_VALID_ERROR);
        }
    }

    protected function addViolationWithCodeToContext(string $message, string $code): void
    {
        $this->context->buildViolation($message)
            ->setCode($code)
            ->atPath('giftVoucherCode')
            ->addViolation();
    }
}
